<?php

namespace App\Http\Modules\Module\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Module\Services\ModuleService;

class ModuleController extends Controller
{
    public function __construct(private ModuleService $moduleService)
    {
    }

    public function obtenerModulos()
    {
        try {
            $modules = $this->moduleService->obtenerModulos();
            return response()->json($modules, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function crearModulo(Request $request)
    {
        try {
            $module = $this->moduleService->crearModulo($request->all());
            return response()->json($module, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerModulo($id)
    {
        try {
            $module = $this->moduleService->obtenerModulo($id);
            return response()->json($module, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function modificarModulo(Request $request, $id)
    {
        try {
            $module = $this->moduleService->modificarModulo($request->all(), $id);
            return response()->json($module, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarModulo($id)
    {
        try {
            $this->moduleService->eliminarModulo($id);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
