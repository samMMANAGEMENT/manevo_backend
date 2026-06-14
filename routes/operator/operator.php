<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Operator\Controller\OperatorController;

Route::prefix('operators')->group(function () {
    Route::get('obtenerOperadores', [OperatorController::class, 'obtenerOperadores']);
    Route::post('crearOperador', [OperatorController::class, 'crearOperador']);
    Route::get('obtenerOperador/{id}', [OperatorController::class, 'obtenerOperador']);
    Route::put('modificarOperador/{id}', [OperatorController::class, 'modificarOperador']);
    Route::delete('eliminarOperador/{id}', [OperatorController::class, 'eliminarOperador']);
});
