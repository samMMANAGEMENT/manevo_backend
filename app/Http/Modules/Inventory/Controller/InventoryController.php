<?php

namespace App\Http\Modules\Inventory\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Inventory\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $products = $this->inventoryService->listProducts(Auth::user()->entity_id);
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'integer|min:0',
            'unit_cost' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'package_size' => 'nullable|integer|min:1',
        ]);

        $validated['entity_id'] = Auth::user()->entity_id;

        $product = $this->inventoryService->createProduct($validated);
        return response()->json($product, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'unit_cost' => 'numeric|min:0',
            'selling_price' => 'numeric|min:0',
            'status' => 'in:active,out_of_stock,inactive',
            'package_size' => 'nullable|integer|min:1',
        ]);

        $product = $this->inventoryService->updateProduct($id, $validated);
        return response()->json($product);
    }

    public function recordMovement(Request $request, $id)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|integer|min:1',
            'date' => 'nullable|date',
        ]);

        $movement = $this->inventoryService->recordMovement($id, $validated['type'], $validated['amount'], $validated['date'] ?? null);
        return response()->json($movement, 201);
    }

    public function movements($id)
    {
        $movements = $this->inventoryService->getMovements($id);
        return response()->json($movements);
    }
}
