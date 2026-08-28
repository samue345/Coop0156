<?php

use App\Http\Controllers\SimulationController;
use App\Models\Customer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('analise');
});

Route::get('/simulacao/{id}', [SimulationController::class, 'show']);

Route::view('/clientes', 'clientes.index');
Route::get('/clientes/create', function () {
    return view('clientes.form', ['customer' => null]);
});
Route::get('/clientes/{customer}/edit', function (Customer $customer) {
    return view('clientes.form', compact('customer'));
});
