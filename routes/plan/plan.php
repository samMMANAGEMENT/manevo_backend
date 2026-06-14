<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Plan\Controller\PlanController;

Route::prefix('plans')->group(function () {
    Route::get('obtenerPlanes', [PlanController::class, 'obtenerPlanes']);
    Route::post('crearPlan', [PlanController::class, 'crearPlan']);
    Route::get('obtenerPlan/{id}', [PlanController::class, 'obtenerPlan']);
    Route::put('modificarPlan/{id}', [PlanController::class, 'modificarPlan']);
    Route::delete('eliminarPlan/{id}', [PlanController::class, 'eliminarPlan']);
    Route::post('sincronizarModulos/{id}', [PlanController::class, 'sincronizarModulos']);
});
