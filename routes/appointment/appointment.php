<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Appointment\Controller\AppointmentController;

Route::middleware('auth:sanctum')->prefix('appointments')->group(function () {
    Route::get('disponibilidad', [AppointmentController::class, 'disponibilidad']);
    Route::get('obtenerCitas', [AppointmentController::class, 'obtenerCitas']);
    Route::get('obtenerCita/{id}', [AppointmentController::class, 'obtenerCita']);
    Route::post('crearCita', [AppointmentController::class, 'crearCita']);
    Route::put('actualizarCita/{id}', [AppointmentController::class, 'actualizarCita']);
    Route::patch('cambiarEstado/{id}', [AppointmentController::class, 'cambiarEstado']);
    Route::patch('marcarPagada/{id}', [AppointmentController::class, 'marcarPagada']);
    Route::delete('eliminarCita/{id}', [AppointmentController::class, 'eliminarCita']);
});
