<?php

namespace App\Http\Modules\Services\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Services\Services\ServicesModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ServicesController extends Controller
{
    protected $servicesModule;

    public function __construct(ServicesModuleService $servicesModule)
    {
        $this->servicesModule = $servicesModule;
    }

    /**
     * Get all active services
     */
    public function obtenerServicios()
    {
        return response()->json($this->servicesModule->listServices());
    }

    /**
     * Create a new service master
     */
    public function crearServicio(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'employee_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $service = $this->servicesModule->createService($validated);
        return response()->json($service, 201);
    }

    /**
     * Update an existing service
     */
    public function actualizarServicio(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'employee_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $service = \App\Http\Modules\Services\Model\Service::findOrFail($id);
        $service->update($validated);

        return response()->json($service);
    }

    /**
     * Process a multiples services order
     */
    public function procesarOrdenServicio(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer,mixed',
            'cash_amount' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
            'transaction_token' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.operator_id' => 'required|exists:operators,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $order = $this->servicesModule->processServicesOrder($validated);
            return response()->json([
                'message' => 'Servicios registrados con éxito',
                'order' => $order
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al procesar el registro',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get order history
     */
    public function obtenerHistorialOrdenes()
    {
        $history = $this->servicesModule->getPerformanceHistory(Auth::user()->entity_id);
        return response()->json($history);
    }
}
