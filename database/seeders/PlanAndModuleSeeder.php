<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanAndModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Módulos
        $modules = [
            ['name' => 'Dashboard', 'slug' => 'dashboard'],
            ['name' => 'POS', 'slug' => 'pos'],
            ['name' => 'Servicios', 'slug' => 'services'],
            ['name' => 'Pagos', 'slug' => 'payments'],
            ['name' => 'Gastos', 'slug' => 'expenses'],
            ['name' => 'Agendas', 'slug' => 'schedules'],
            ['name' => 'Integraciones', 'slug' => 'integrations'],
            ['name' => 'Reportes', 'slug' => 'reports'],
            ['name' => 'Configuraciones Avanzadas', 'slug' => 'settings_advanced'],
            ['name' => 'Administración', 'slug' => 'admin'],
            ['name' => 'API Access', 'slug' => 'api_access'],
            ['name' => 'Inventario', 'slug' => 'inventory'],
            ['name' => 'Facturación', 'slug' => 'billing'],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['slug' => $module['slug']],
                [
                    'name' => $module['name'],
                    'status' => true,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }

        // 2. Crear Planes
        $plans = [
            [
                'name' => 'Gratis',
                'slug' => 'free',
                'price' => 0.00,
                'duration' => 3650, // 10 años aprox (ilimitado por lógica de negocio)
                'max_users' => 2,
                'is_default' => true,
                'status' => true,
            ],
            [
                'name' => 'Goo',
                'slug' => 'goo',
                'price' => 9.99,
                'duration' => 30,
                'max_users' => 5,
                'is_default' => false,
                'status' => true,
            ],
            [
                'name' => 'Essential',
                'slug' => 'essential',
                'price' => 19.99,
                'duration' => 30,
                'max_users' => 0, // Ilimitado
                'is_default' => false,
                'status' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'price' => 99.99,
                'duration' => 30,
                'max_users' => 0, // Ilimitado
                'is_default' => false,
                'status' => true,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $plan['slug']],
                array_merge($plan, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        // 3. Asignar Módulos a Planes (pivot: plan_module)
        $freePlanId = DB::table('plans')->where('slug', 'free')->value('id');
        $gooPlanId = DB::table('plans')->where('slug', 'goo')->value('id');
        $essentialPlanId = DB::table('plans')->where('slug', 'essential')->value('id');
        $businessPlanId = DB::table('plans')->where('slug', 'business')->value('id');

        $allModules = DB::table('modules')->get();

        foreach ($allModules as $module) {
            // --- PLAN FREE (Módulos base incluidos en todos los planes) ---
            if (in_array($module->slug, ['dashboard', 'pos', 'services', 'payments', 'settings_advanced'])) {
                $this->assignToPlans($module->id, [$freePlanId, $gooPlanId, $essentialPlanId, $businessPlanId]);
            }

            // --- PLAN GO (Añade Gastos, Agendas e Integraciones) ---
            if (in_array($module->slug, ['expenses', 'schedules', 'integrations'])) {
                $this->assignToPlans($module->id, [$gooPlanId, $essentialPlanId, $businessPlanId]);
            }

            // --- PLAN ESSENTIAL (Añade Reportes y Administración) ---
            if (in_array($module->slug, ['reports', 'admin'])) {
                $this->assignToPlans($module->id, [$essentialPlanId, $businessPlanId]);
            }

            // --- PLAN BUSINESS (Añade API Access / Acceso Total) ---
            if ($module->slug === 'api_access') {
                $this->assignToPlans($module->id, [$businessPlanId]);
            }

            // --- INVENTARIO (Goo en adelante) ---
            if ($module->slug === 'inventory') {
                $this->assignToPlans($module->id, [$gooPlanId, $essentialPlanId, $businessPlanId]);
            }
            // --- FACTURACIÓN (Essential en adelante) ---
            if ($module->slug === 'billing') {
                $this->assignToPlans($module->id, [$essentialPlanId, $businessPlanId]);
            }
        }
    }

    /**
     * Helper para asignar un módulo a varios planes
     */
    private function assignToPlans(int $moduleId, array $planIds): void
    {
        foreach ($planIds as $planId) {
            DB::table('plan_module')->updateOrInsert(
                ['plan_id' => $planId, 'module_id' => $moduleId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
