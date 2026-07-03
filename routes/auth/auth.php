<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Auth\Controller\AuthController;
use App\Http\Modules\Auth\Controller\UserController;
use App\Http\Modules\Auth\Controller\SaaSAdminController;

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

        // SaaS Super Admin
        Route::get('saas-admin/workspaces', [SaaSAdminController::class, 'obtenerWorkspacesPlataforma']);
        Route::get('saas-admin/planes', [SaaSAdminController::class, 'obtenerPlanesSaaS']);
        Route::post('saas-admin/modificar-plan', [SaaSAdminController::class, 'modificarPlanEntidad']);
        Route::get('saas-admin/addons', [SaaSAdminController::class, 'obtenerAddonsSaaS']);
        Route::post('saas-admin/activar-addon', [SaaSAdminController::class, 'activarAddonEntidad']);
        Route::post('saas-admin/desactivar-addon', [SaaSAdminController::class, 'desactivarAddonEntidad']);
    });
});