<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Modules\Inventory\Model\Product;
use App\Http\Modules\Inventory\Model\InventoryMovement;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenemos todas las entidades (workspaces) disponibles
        $entities = DB::table('entities')->get();

        if ($entities->isEmpty()) {
            $this->command->warn('No se encontraron entidades para asociar los productos.');
            return;
        }

        foreach ($entities as $entity) {
            // Buscamos el primer usuario de esta entidad para ser el responsable del stock inicial
            $user = User::where('entity_id', $entity->id)->first();

            if (!$user) {
                $this->command->warn("No se encontró un usuario para la entidad {$entity->name}, saltando...");
                continue;
            }

            $products = [
                [
                    'name' => 'Aceite Motul 10W-40 4T',
                    'quantity' => 20,
                    'unit_cost' => 35000,
                    'selling_price' => 55000,
                    'status' => 'active',
                    'package_size' => 12,
                ],
                [
                    'name' => 'Pastillas de Freno (Universal)',
                    'quantity' => 15,
                    'unit_cost' => 12000,
                    'selling_price' => 25000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Kit de Arrastre Pulsar 200NS',
                    'quantity' => 8,
                    'unit_cost' => 120000,
                    'selling_price' => 185000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Guaya de Acelerador Honda CB190',
                    'quantity' => 25,
                    'unit_cost' => 8000,
                    'selling_price' => 15000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Bujía NGK Iridium',
                    'quantity' => 30,
                    'unit_cost' => 18000,
                    'selling_price' => 32000,
                    'status' => 'active',
                    'package_size' => 10,
                ],
                [
                    'name' => 'Casco Integral LS2 Rapid Black',
                    'quantity' => 0,
                    'unit_cost' => 220000,
                    'selling_price' => 310000,
                    'status' => 'out_of_stock',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Llamta Michelin Pilot Street 130/70',
                    'quantity' => 6,
                    'unit_cost' => 205000,
                    'selling_price' => 290000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
            ];

            foreach ($products as $data) {
                // Usamos updateOrCreate para evitar duplicados si corres el seeder varias veces
                $product = Product::updateOrCreate(
                    [
                        'entity_id' => $entity->id,
                        'name' => $data['name']
                    ],
                    $data
                );

                // Si el producto es nuevo y tiene stock inicial, registrar el movimiento de entrada
                if ($product->wasRecentlyCreated && $product->quantity > 0) {
                    InventoryMovement::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'type' => 'in',
                        'previous_quantity' => 0,
                        'movement_quantity' => $product->quantity,
                        'new_quantity' => $product->quantity,
                        'date' => now(),
                    ]);
                }
            }
        }

        $this->command->info('¡Productos de ejemplo creados con éxito!');
    }
}
