<?php

use App\Http\Controllers\CreditAnalysisController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MockBureauController;
use Illuminate\Support\Facades\Route;

// --- CRUD de Clientes (o candidato implementa a lógica) ---
Route::apiResource('clientes', CustomerController::class)
    ->parameters(['clientes' => 'customer']);

// --- Análise de Crédito ---
Route::post('/analise-credito', [CreditAnalysisController::class, 'requestAnalysis'])
    ->middleware('throttle:credit-analysis');
Route::post('/analise-credito/{creditAnalysis}/contratar', [CreditAnalysisController::class, 'contract']);

// --- Endpoint de Mock (Bureau de Crédito externo simulado — não alterar) ---
Route::get('/mock/bureau/{cpf}', [MockBureauController::class, 'consultar']);
