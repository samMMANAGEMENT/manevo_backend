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
                    'name' => 'Shampoo Profesional Kerastase 250ml',
                    'quantity' => 15,
                    'unit_cost' => 65000,
                    'selling_price' => 95000,
                    'status' => 'active',
                    'package_size' => 12,
                ],
                [
                    'name' => 'Esmalte de Uñas Masglo (Tonos Variados)',
                    'quantity' => 40,
                    'unit_cost' => 5000,
                    'selling_price' => 12000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Tratamiento Capilar Reparador 500ml',
                    'quantity' => 12,
                    'unit_cost' => 32000,
                    'selling_price' => 55000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Aceite de Argán Hidratante 100ml',
                    'quantity' => 20,
                    'unit_cost' => 28000,
                    'selling_price' => 48000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Crema Exfoliante Facial Orgánica 200g',
                    'quantity' => 18,
                    'unit_cost' => 18000,
                    'selling_price' => 35000,
                    'status' => 'active',
                    'package_size' => 10,
                ],
                [
                    'name' => 'Mascarilla de Arcilla Purificante 150g',
                    'quantity' => 25,
                    'unit_cost' => 12000,
                    'selling_price' => 25000,
                    'status' => 'active',
                    'package_size' => 1,
                ],
                [
                    'name' => 'Secador de Cabello Profesional 2200W',
                    'quantity' => 0,
                    'unit_cost' => 150000,
                    'selling_price' => 230000,
                    'status' => 'out_of_stock',
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
