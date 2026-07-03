<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Addon\Controller\AddonController;

Route::middleware('auth:sanctum')->prefix('addons')->group(function () {
    Route::get('obtenerAddonesDisponibles', [AddonController::class, 'obtenerAddonesDisponibles']);
    Route::get('obtenerAddonesDeMiEntidad', [AddonController::class, 'obtenerAddonesDeMiEntidad']);
    Route::get('{slug}/configuracion', [AddonController::class, 'obtenerConfiguracion']);
    Route::put('{slug}/configuracion', [AddonController::class, 'actualizarConfiguracion']);
});
