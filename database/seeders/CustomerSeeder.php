<?php

namespace Database\Seeders;

use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Entity\Model\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $entities = Entity::all();

        foreach ($entities as $entity) {
            Customer::updateOrCreate(
                [
                    'entity_id' => $entity->id,
                    'identification_number' => '2222'
                ],
                [
                    'name' => 'CONSUMIDOR FINAL',
                    'identification_type' => 'CC',
                    'email' => 'consumidor@final.com',
                    'municipality_id' => 822,
                    'type_regime_id' => 2,
                    'type_organization_id' => 2,
                    'status' => true,
                ]
            );
        }
    }
}
