<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Plan\Model\Plan;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear o verificar la Entidad (Workspace) demo
        $entity = Entity::firstOrCreate(
            ['name' => 'Entidad Manevo Demo'],
            [
                'description' => 'Entidad principal de demostración y pruebas',
                'status' => true,
            ]
        );

        // 2. Asociar la Entidad al Plan "Business" para habilitar todos los módulos
        $plan = Plan::where('slug', 'business')->first();
        if ($plan) {
            $hasPlan = DB::table('entity_plan')
                ->where('entity_id', $entity->id)
                ->where('plan_id', $plan->id)
                ->exists();

            if (!$hasPlan) {
                $entity->planes()->attach($plan->id, [
                    'start_date' => now(),
                    'end_date' => null, // Ilimitado
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Crear el Usuario Administrador (ID 1)
        User::firstOrCreate(
            ['email' => 'admin@manevo.com'],
            [
                'name' => 'Administrador Manevo',
                'password' => Hash::make('password'),
                'entity_id' => $entity->id,
                'status' => true,
            ]
        );

        // 4. Crear el Usuario Super Admin (sapinedal05@outlook.com)
        User::firstOrCreate(
            ['email' => 'sapinedal05@outlook.com'],
            [
                'name' => 'Samuel Pineda',
                'password' => Hash::make('password'),
                'entity_id' => $entity->id,
                'status' => true,
            ]
        );
    }
}
