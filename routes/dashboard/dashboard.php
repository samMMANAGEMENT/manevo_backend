<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Dashboard\Controller\DashboardController;

Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('summary', [DashboardController::class, 'getSummary']);
    Route::get('daily-report', [DashboardController::class, 'getDailyReport']);
});
