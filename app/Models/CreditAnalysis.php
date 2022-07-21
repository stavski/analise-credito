<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome', 'cpf', 'negativado', 'salario', 'limite_cartao', 'valor_aluguel', 'rua', 'numero', 'municipio', 'unidade_federativa', 'cep', 'created_at', 'updated_at'
    ];
}
