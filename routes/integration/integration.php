<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Integration\Controller\N8nIntegrationController;

// Server-to-server: autenticado por API key (header X-API-Key), no por sesión de usuario.
Route::middleware('n8n.apikey')->prefix('integrations/n8n')->group(function () {
    Route::get('addons/{slug}/entities/{entity}/status', [N8nIntegrationController::class, 'estadoAddon']);

    Route::get('appointments/due-reminders', [N8nIntegrationController::class, 'recordatoriosPendientes']);
    Route::post('appointments/{appointment}/reminder-sent', [N8nIntegrationController::class, 'marcarRecordatorioEnviado']);
    Route::post('appointments/{appointment}/payment-notified', [N8nIntegrationController::class, 'marcarPagoNotificado']);

    Route::get('entities/{entity}/services', [N8nIntegrationController::class, 'serviciosDeEntidad']);
    Route::get('entities/{entity}/available-slots', [N8nIntegrationController::class, 'disponibilidad']);
    Route::post('entities/{entity}/appointments', [N8nIntegrationController::class, 'crearCita']);
});
