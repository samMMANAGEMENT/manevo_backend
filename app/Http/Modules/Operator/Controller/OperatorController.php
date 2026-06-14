<?php

namespace App\Http\Modules\Operator\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Modules\Operator\Services\OperatorService;

class OperatorController extends Controller
{
    public function __construct(private OperatorService $operatorService)
    {
    }

    public function obtenerOperadores()
    {
        try {
            $operators = $this->operatorService->obtenerOperadores();
            return response()->json($operators, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function crearOperador(Request $request)
    {
        try {
            $operator = $this->operatorService->crearOperador($request->all());
            return response()->json($operator, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerOperador($id)
    {
        try {
            $operator = $this->operatorService->obtenerOperador($id);
            return response()->json($operator, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function modificarOperador(Request $request, $id)
    {
        try {
            $operator = $this->operatorService->modificarOperador($request->all(), $id);
            return response()->json($operator, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarOperador($id)
    {
        try {
            $this->operatorService->eliminarOperador($id);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
