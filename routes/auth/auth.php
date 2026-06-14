<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Auth\Controller\AuthController;
use App\Http\Modules\Auth\Controller\UserController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // User CRUD
        Route::get('obtenerUsuarios', [UserController::class, 'obtenerUsuarios']);
        Route::post('guardarUsuario', [UserController::class, 'guardarUsuario']);
        Route::get('obtenerRoles', [UserController::class, 'obtenerRoles']);
    });
});