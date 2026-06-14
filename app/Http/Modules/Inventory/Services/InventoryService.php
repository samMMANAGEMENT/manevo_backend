<?php

namespace App\Http\Modules\Inventory\Services;

use App\Http\Modules\Inventory\Model\Product;
use App\Http\Modules\Inventory\Model\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    public function listProducts($entityId)
    {
        return Product::where('entity_id', $entityId)->get();
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            // Si el producto tiene cantidad inicial > 0, creamos un movimiento de entrada
            if ($product->quantity > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'previous_quantity' => 0,
                    'movement_quantity' => $product->quantity,
                    'new_quantity' => $product->quantity,
                    'date' => now(),
                ]);
            }

            return $product;
        });
    }

    public function updateProduct($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        return $product;
    }

    public function recordMovement($productId, $type, $amount, $date = null)
    {
        return DB::transaction(function () use ($productId, $type, $amount, $date) {
            $product = Product::lockForUpdate()->findOrFail($productId);

            $previousQuantity = $product->quantity;
            $movementAmount = (int) $amount;

            if ($type === 'in') {
                $newQuantity = $previousQuantity + $movementAmount;
            } else {
                $newQuantity = $previousQuantity - $movementAmount;
            }

            // Actualizar stock y estado
            $product->quantity = $newQuantity;

            if ($newQuantity <= 0) {
                $product->status = 'out_of_stock';
            } elseif ($product->status === 'out_of_stock' && $newQuantity > 0) {
                $product->status = 'active';
            }

            $product->save();

            // Guardar movimiento
            return InventoryMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => $type,
                'previous_quantity' => $previousQuantity,
                'movement_quantity' => $movementAmount,
                'new_quantity' => $newQuantity,
                'date' => $date ?? now(),
            ]);
        });
    }

    public function getMovements($productId)
    {
        return InventoryMovement::with('user')
            ->where('product_id', $productId)
            ->orderBy('date', 'desc')
            ->get();
    }
}
