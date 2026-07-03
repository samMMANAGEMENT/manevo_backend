<?php

namespace App\Http\Modules\Integration\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Integration\Services\N8nIntegrationService;
use Illuminate\Http\Request;

class N8nIntegrationController extends Controller
{
    public function __construct(private N8nIntegrationService $service)
    {
    }

    public function estadoAddon($slug, $entity)
    {
        try {
            return response()->json($this->service->estadoAddon((int) $entity, $slug), 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 404);
        }
    }

    public function recordatoriosPendientes()
    {
        try {
            return response()->json($this->service->recordatoriosPendientes(), 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function marcarRecordatorioEnviado(Request $request, $appointment)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:sent,failed',
            'external_id' => 'nullable|string',
        ]);

        try {
            $this->service->marcarRecordatorioEnviado(
                (int) $appointment,
                $validated['status'] ?? 'sent',
                $validated['external_id'] ?? null
            );
            return response()->json(['message' => 'Registrado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function serviciosDeEntidad($entity)
    {
        try {
            return response()->json($this->service->serviciosDeEntidad((int) $entity), 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function disponibilidad(Request $request, $entity)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'operator_id' => 'nullable|integer',
            'duration_minutes' => 'nullable|integer|min:5',
        ]);

        try {
            $slots = $this->service->disponibilidad(
                (int) $entity,
                $validated['operator_id'] ?? null,
                $validated['date'],
                $validated['duration_minutes'] ?? 60
            );
            return response()->json(['slots' => $slots], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function crearCita(Request $request, $entity)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'operator_id' => 'nullable|exists:operators,id',
            'service_id' => 'nullable|exists:services,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5',
            'notes' => 'nullable|string',
        ]);

        try {
            $cita = $this->service->crearCitaDesdeBot((int) $entity, $validated);
            return response()->json($cita, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function marcarPagoNotificado(Request $request, $appointment)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:sent,failed',
            'external_id' => 'nullable|string',
        ]);

        try {
            $this->service->marcarPagoNotificado(
                (int) $appointment,
                $validated['status'] ?? 'sent',
                $validated['external_id'] ?? null
            );
            return response()->json(['message' => 'Registrado'], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
