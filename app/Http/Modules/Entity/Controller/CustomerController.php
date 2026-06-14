<?php

namespace App\Http\Modules\Entity\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Entity\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function obtenerClientes()
    {
        return response()->json($this->customerService->obtenerClientes());
    }

    public function guardarCliente(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'identification_type' => 'required|string',
            'identification_number' => 'required|string',
            'dv' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'municipality_id' => 'nullable|integer',
            'type_regime_id' => 'nullable|integer',
            'type_organization_id' => 'nullable|integer',
        ]);

        try {
            $cliente = $this->customerService->crearCliente($validated);
            return response()->json($cliente, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear cliente: ' . $e->getMessage()], 422);
        }
    }

    public function modificarCliente($id, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'identification_type' => 'required|string',
            'identification_number' => 'required|string',
            'dv' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'municipality_id' => 'nullable|integer',
            'type_regime_id' => 'nullable|integer',
            'type_organization_id' => 'nullable|integer',
        ]);

        try {
            $cliente = $this->customerService->modificarCliente($id, $validated);
            return response()->json($cliente);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al modificar cliente: ' . $e->getMessage()], 422);
        }
    }

    public function eliminarCliente($id)
    {
        try {
            $this->customerService->eliminarCliente($id);
            return response()->json(['message' => 'Cliente eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar cliente'], 422);
        }
    }
}
