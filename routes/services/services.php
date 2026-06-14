<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Services\Controller\ServicesController;

Route::middleware('auth:sanctum')->prefix('services-module')->group(function () {
    Route::get('obtenerServicios', [ServicesController::class, 'obtenerServicios']);
    Route::post('crearServicio', [ServicesController::class, 'crearServicio']);
    Route::put('actualizarServicio/{id}', [ServicesController::class, 'actualizarServicio']);
    Route::post('procesarOrdenServicio', [ServicesController::class, 'procesarOrdenServicio']);
    Route::get('obtenerHistorialOrdenes', [ServicesController::class, 'obtenerHistorialOrdenes']);
});
