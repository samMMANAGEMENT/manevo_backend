<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Modules\Services\Model\Service;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $entities = DB::table('entities')->get();

        foreach ($entities as $entity) {
            $services = [
                [
                    'entity_id' => $entity->id,
                    'name' => 'Lavado General Moto',
                    'price' => 15000,
                    'employee_percentage' => 40,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Lavado Especial (Polichado)',
                    'price' => 35000,
                    'employee_percentage' => 35,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Cambio de Aceite (Solo Mano de Obra)',
                    'price' => 8000,
                    'employee_percentage' => 50,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Mantenimiento Preventivo',
                    'price' => 120000,
                    'employee_percentage' => 30,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Sincronización',
                    'price' => 65000,
                    'employee_percentage' => 40,
                    'status' => true,
                ],
            ];

            foreach ($services as $service) {
                Service::updateOrCreate(
                    ['entity_id' => $entity->id, 'name' => $service['name']],
                    $service
                );
            }
        }
    }
}
