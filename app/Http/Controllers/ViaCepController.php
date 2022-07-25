<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ViaCepController extends Controller
{
    public function consultarCep($cep)
    {
        return Http::get('viacep.com.br/ws/'.preg_replace("/[^0-9]/", "", $cep).'/json/')->json();
    }
}
