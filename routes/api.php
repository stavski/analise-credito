<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreditAnalysisController;
use App\Models\CreditAnalysis;

Route::post('analise-credito', [CreditAnalysisController::class, 'analiseDeCredito']);