<?php

namespace App\Http\Modules\Plan\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Plan\Services\PlanService;

class PlanController extends Controller
{
    public function __construct(private PlanService $planService)
    {
    }

    public function obtenerPlanes()
    {
        try {
            $plans = $this->planService->obtenerPlanes();
            return response()->json($plans, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function crearPlan(Request $request)
    {
        try {
            $plan = $this->planService->crearPlan($request->all());
            return response()->json($plan, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerPlan($id)
    {
        try {
            $plan = $this->planService->obtenerPlan($id);
            return response()->json($plan, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function modificarPlan(Request $request, $id)
    {
        try {
            $plan = $this->planService->modificarPlan($request->all(), $id);
            return response()->json($plan, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarPlan($id)
    {
        try {
            $this->planService->eliminarPlan($id);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function sincronizarModulos(Request $request, $id)
    {
        try {
            $plan = $this->planService->sincronizarModulos($id, $request->get('modulos', []));
            return response()->json($plan, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
