<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Module\Controller\ModuleController;

Route::prefix('modules')->group(function () {
    Route::get('obtenerModulos', [ModuleController::class, 'obtenerModulos']);
    Route::post('crearModulo', [ModuleController::class, 'crearModulo']);
    Route::get('obtenerModulo/{id}', [ModuleController::class, 'obtenerModulo']);
    Route::put('modificarModulo/{id}', [ModuleController::class, 'modificarModulo']);
    Route::delete('eliminarModulo/{id}', [ModuleController::class, 'eliminarModulo']);
});
