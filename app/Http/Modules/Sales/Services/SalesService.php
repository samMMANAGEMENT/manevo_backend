<?php

namespace App\Http\Modules\Sales\Services;

use App\Http\Modules\Sales\Model\Sale;
use App\Http\Modules\Sales\Model\SaleItem;
use App\Http\Modules\Inventory\Model\Product;
use App\Http\Modules\Inventory\Services\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class SalesService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function processSale(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $items = $data['items']; // Array de ['product_id', 'quantity']

            $totalSale = 0;
            $totalProfit = 0;
            $saleDetails = [];

            // 1. Validaciones previas y cálculos
            foreach ($items as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);

                if ($product->quantity < $itemData['quantity']) {
                    throw new Exception("Stock insuficiente para el producto: {$product->name}. Disponible: {$product->quantity}");
                }

                $subtotal = $product->selling_price * $itemData['quantity'];
                $unitProfit = $product->selling_price - $product->unit_cost;
                $itemProfit = $unitProfit * $itemData['quantity'];

                $totalSale += $subtotal;
                $totalProfit += $itemProfit;

                $saleDetails[] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price_at_sale' => $product->selling_price,
                    'cost_at_sale' => $product->unit_cost,
                    'subtotal' => $subtotal
                ];
            }

            // 2. Crear el encabezado de la venta
            $sale = Sale::create([
                'entity_id' => $user->entity_id,
                'seller_id' => $user->id,
                'total' => $totalSale,
                'total_profit' => $totalProfit,
                'payment_method' => $data['payment_method'],
                'cash_amount' => $data['cash_amount'] ?? ($data['payment_method'] === 'cash' ? $totalSale : 0),
                'transfer_amount' => $data['transfer_amount'] ?? ($data['payment_method'] === 'transfer' ? $totalSale : 0),
                'date' => now(),
            ]);

            // 3. Registrar detalles y actualizar stock
            foreach ($saleDetails as $detail) {
                // Registrar detalle
                SaleItem::create(array_merge($detail, ['sale_id' => $sale->id]));

                // Reducir stock mediante el servicio de inventario para mantener la trazabilidad
                $this->inventoryService->recordMovement(
                    $detail['product_id'],
                    'out',
                    $detail['quantity'],
                    now()
                );
            }

            return $sale->load('items.product');
        });
    }

    public function getSalesHistory($entityId)
    {
        return Sale::with(['items.product', 'seller.operator'])
            ->where('entity_id', $entityId)
            ->orderBy('date', 'desc')
            ->get();
    }
}
