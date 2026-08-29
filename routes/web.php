<?php

use App\Http\Controllers\ContractedCreditController;
use App\Http\Controllers\SimulationController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('credit-analysis');
});

Route::get('/simulacao/{creditAnalysis}', [SimulationController::class, 'show']);
Route::get('/contratacoes', [ContractedCreditController::class, 'index']);

Route::view('/clientes', 'customers.index');
Route::get('/clientes/create', function () {
    return view('customers.form', ['customer' => null]);
});
Route::get('/clientes/{customer}/edit', function (Customer $customer) {
    return view('customers.form', compact('customer'));
});

