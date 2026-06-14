<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Entity\Controller\EntityController;
use App\Http\Modules\Entity\Controller\BillingController;

Route::middleware('auth:sanctum')->prefix('entities')->group(function () {
    Route::get('miEntidad', [EntityController::class, 'miEntidad']);
    Route::get('obtenerEntidades', [EntityController::class, 'obtenerEntidades']);
    Route::post('crearEntidad', [EntityController::class, 'crearEntidad']);
    Route::get('obtenerEntidad/{id}', [EntityController::class, 'obtenerEntidad']);
    Route::put('modificarEntidad/{id}', [EntityController::class, 'modificarEntidad']);
    Route::delete('eliminarEntidad/{id}', [EntityController::class, 'eliminarEntidad']);
    Route::post('asignarPlan/{id}', [EntityController::class, 'asignarPlan']);
    Route::post('cambiarEntidad', [EntityController::class, 'cambiarEntidad']);
});

Route::middleware('auth:sanctum')->prefix('billing-config')->group(function () {
    Route::get('obtenerConfiguracion', [BillingController::class, 'obtenerConfiguracion']);
    Route::post('guardarConfiguracion', [BillingController::class, 'guardarConfiguracion']);
});

Route::middleware('auth:sanctum')->prefix('customers')->group(function () {
    Route::get('obtenerClientes', [\App\Http\Modules\Entity\Controller\CustomerController::class, 'obtenerClientes']);
    Route::post('guardarCliente', [\App\Http\Modules\Entity\Controller\CustomerController::class, 'guardarCliente']);
    Route::put('modificarCliente/{id}', [\App\Http\Modules\Entity\Controller\CustomerController::class, 'modificarCliente']);
    Route::delete('eliminarCliente/{id}', [\App\Http\Modules\Entity\Controller\CustomerController::class, 'eliminarCliente']);
});
