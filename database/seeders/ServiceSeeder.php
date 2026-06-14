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
                    'name' => 'Corte de Cabello Dama/Caballero',
                    'price' => 25000,
                    'employee_percentage' => 40,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Manicure y Pedicure Semi-permanente',
                    'price' => 45000,
                    'employee_percentage' => 50,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Balayage / Tintura Completa',
                    'price' => 180000,
                    'employee_percentage' => 35,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Hidratación Capilar Profunda (Keratina)',
                    'price' => 120000,
                    'employee_percentage' => 40,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Limpieza Facial Profunda',
                    'price' => 80000,
                    'employee_percentage' => 45,
                    'status' => true,
                ],
                [
                    'entity_id' => $entity->id,
                    'name' => 'Masaje Relajante Corporal (1 Hora)',
                    'price' => 90000,
                    'employee_percentage' => 50,
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
