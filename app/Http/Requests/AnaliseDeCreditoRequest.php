<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnaliseDeCreditoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nome' => 'required',
            'cpf' => 'required',
            'negativado' => 'required',
            'salario' => 'required',
            'limiteCartao' => 'required',
            'valorAluguel' => 'required',
            'rua' => 'required',
            'numero' => 'required',
            'municipio' => 'required',
            'unidadeFederativa' => 'required',
            'cep' => ['required'],
        ];
    }
}
