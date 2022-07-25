<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_analysis_id', 'pontuacao_final', 'resultado', 'created_at', 'updated_at'
    ];
}
