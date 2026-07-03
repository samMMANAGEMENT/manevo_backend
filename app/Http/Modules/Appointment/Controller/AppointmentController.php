<?php

namespace App\Http\Modules\Appointment\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Appointment\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointmentService)
    {
    }

    public function obtenerCitas(Request $request)
    {
        try {
            $citas = $this->appointmentService->listar($request->only(['from', 'to', 'operator_id', 'status']));
            return response()->json($citas, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerCita($id)
    {
        try {
            $cita = $this->appointmentService->obtener($id);
            return response()->json($cita, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function crearCita(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'operator_id' => 'nullable|exists:operators,id',
            'service_id' => 'nullable|exists:services,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5',
            'price' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        try {
            $cita = $this->appointmentService->crear(array_merge($validated, [
                'entity_id' => Auth::user()->entity_id,
                'source' => 'manual',
            ]));
            return response()->json($cita, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function actualizarCita(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|exists:customers,id',
            'operator_id' => 'sometimes|nullable|exists:operators,id',
            'service_id' => 'sometimes|nullable|exists:services,id',
            'scheduled_at' => 'sometimes|date',
            'duration_minutes' => 'sometimes|integer|min:5',
            'price' => 'sometimes|numeric',
            'notes' => 'sometimes|nullable|string',
        ]);

        try {
            $cita = $this->appointmentService->actualizar($id, $validated);
            return response()->json($cita, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        try {
            $cita = $this->appointmentService->cambiarEstado($id, $validated['status']);
            return response()->json($cita, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function marcarPagada($id)
    {
        try {
            $cita = $this->appointmentService->marcarPagada($id);
            return response()->json($cita, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function eliminarCita($id)
    {
        try {
            $this->appointmentService->eliminar($id);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function disponibilidad(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'operator_id' => 'nullable|exists:operators,id',
            'duration_minutes' => 'nullable|integer|min:5',
        ]);

        try {
            $slots = $this->appointmentService->disponibilidad(
                Auth::user()->entity_id,
                $validated['operator_id'] ?? null,
                $validated['date'],
                $validated['duration_minutes'] ?? 60
            );
            return response()->json(['slots' => $slots], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
