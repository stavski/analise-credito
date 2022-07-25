<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnaliseDeCreditoRequest;
use Illuminate\Http\Request;
use App\Models\CreditAnalysis;
use App\Models\Result;
use Exception;
use Illuminate\Support\Facades\DB;

class CreditAnalysisController extends Controller
{
    public function analiseDeCredito(AnaliseDeCreditoRequest $request)
    {
        $validaCep = new ViaCepController();
        $retorno   = $validaCep->consultarCep($request->cep);

        if (!$retorno) {
            return response()->json(['errors' => ['cep' => ['Cep não encontrado']]], 422, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($retorno['localidade'] != $request->municipio) {
            return response()->json(['errors' => ['municipio' => ['Munícipio não corresponde ao cep informado']]], 422, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        if ($retorno['uf'] != $request->unidadeFederativa) {
            return response()->json(['errors' => ['unidadeFederativa' => ['Unidade federativa não corresponde ao cep informado']]], 422, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        $resultado = $this->calcularPontuacao($request);
        $status    = $this->verificaStatus($resultado);
        $codigo    = $this->store($request, $resultado['pontos'], $status);
        
        if (!$codigo) {
            return response()->json(['errors' => ['pontuacao' => ['Não foi possível salvar os dados no banco de dados']]], 422, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        $json = array(
            'codigo' => $codigo,
            'pontuacao' => $resultado['pontos'],
            'resultado' => $status
        );


        return response()->json($json, 422);
    }

    protected function calcularPontuacao($request)
    {
        // Pontos iniciais da consulta
        $pontos        = 100;
        $parametroUm   = false;
        $parametroDois = false;

        // Caso o valor do aluguel ultrapasse 30% do salário, sua pontuação deve ser decrescida em 18%;
        $trintaPorcentoSalario = ($request->salario / 100) * 30;

        if ($request->valorAluguel > $trintaPorcentoSalario) {
            $pontos      -= ($pontos / 100) * 18;
            $parametroUm = true;
        }

        // Caso o cliente esteja com seu CPF negativado, sua pontuação deve ser decrescida em 31%.
        if (true === $request->negativado) {
            $pontos        -= ($pontos / 100) * 31;
            $parametroDois = true;
        }

        // Caso o limite disponível no cartão do cliente seja menor ou igual ao valor mensal de aluguel, sua pontuação deve ser decrescida em 15%.
        if ($request->limiteCartao <= $request->valorAluguel) {
            $pontos -= ($pontos / 100) * 15;
        }

        // Caso o cliente já tenha realizada uma análise de crédito nos últimos 90 dias e esta análise que tenha sido reprovada, sua pontuação deve ser decrescida em 10%.
        $creditAnalysis = CreditAnalysis::where('cpf', $request->cpf)->first();

        if ($creditAnalysis) {
            $ultimaConsulta = Result::where('credit_analysis_id', $creditAnalysis->id)
                ->whereBetween('created_at', array(date('Y-m-d H:i:s', strtotime('-90 days')), date('Y-m-d H:i:s')))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($ultimaConsulta && $ultimaConsulta->resultado == 'Reprovado') {
                $pontos -= ($pontos / 100) * 10;
            }
        }

        $resultado['pontos']        = ceil($pontos);
        $resultado['parametroUm']   = $parametroUm;
        $resultado['parametroDois'] = $parametroDois;

        return $resultado;
    }

    protected function verificaStatus($resultado)
    {
        if ($resultado['parametroUm'] && $resultado['parametroDois']) {
            $status = 'Reprovado';
        } else if ($resultado['pontos'] <= 30) {
            $status = 'Reprovado';
        } else if ($resultado['pontos'] > 30 && $resultado['pontos'] < 60) {
            $status = 'Derivado';
        } else if ($resultado['pontos'] >= 60) {
            $status = 'Aprovado';
        }

        return $status;
    }

    protected function store($request, $pontos, $status)
    {
        DB::beginTransaction();

        try {
            // Caso não tenha cadastro é criado um novo
            $creditAnalysis = CreditAnalysis::where('cpf', $request->cpf)->first();

            if (!$creditAnalysis) {
                $credit                     = new CreditAnalysis();
                $credit->nome               = $request->nome;
                $credit->cpf                = $request->cpf;
                $credit->negativado         = $request->negativado ? 1 : 0;
                $credit->salario            = $request->salario;
                $credit->limite_cartao      = $request->limiteCartao;
                $credit->valor_aluguel      = $request->valorAluguel;
                $credit->rua                = $request->rua;
                $credit->numero             = $request->numero;
                $credit->municipio          = $request->municipio;
                $credit->unidade_federativa = $request->unidadeFederativa;
                $credit->cep                = $request->cep;
                $credit->save();
            }

            // Cadastra o resultado da consulta
            $result                     = new Result();
            $result->credit_analysis_id = $credit->id ?? $creditAnalysis->id;
            $result->pontuacao_final    = $pontos;
            $result->resultado          = $status;
            $result->save();

            DB::commit();

            return $result->id;
        } catch (Exception $e) {
            DB::rollBack();

            return false;
        }
    }

    public function ultimaAnaliseDeCredito(Request $request)
    {
        if (!$request->cpf) {
            return response()->json(['errors' => ['cpf' => ['CPF não encontrado']]], 422, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }

        $creditAnalysis = CreditAnalysis::where('cpf', $request->cpf)
            ->with('result')
            ->first();

        if ($creditAnalysis) {
            $json = array(
                'codigo' => $creditAnalysis['result']->last()['id'],
                'pontuacao' => $creditAnalysis['result']->last()['pontuacao_final'],
                'resultado' => $creditAnalysis['result']->last()['resultado'],
                'data' => $creditAnalysis['result']->last()['created_at'],
            );

            return response()->json($json, 422);
        } else {
            return response()->json(['errors' => ['analise' => ['Nenhuma análise encontrada']]], 422, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
        }
    }
}
