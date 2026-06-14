<?php

namespace App\Http\Modules\Sales\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Sales\Services\SalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class SalesController extends Controller
{
    protected $salesService;

    public function __construct(SalesService $salesService)
    {
        $this->salesService = $salesService;
    }

    public function index()
    {
        $sales = $this->salesService->getSalesHistory(Auth::user()->entity_id);
        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer,mixed',
            'cash_amount' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $sale = $this->salesService->processSale($validated);
            return response()->json([
                'message' => 'Venta procesada con éxito',
                'sale' => $sale
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la venta',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
