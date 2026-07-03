<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanAndModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Modelo actual: software 100% gratis (un solo plan, con todos los módulos).
     * El cobro ya no vive en el sistema de Plan/Módulo sino en el de Addons
     * (ver AddonSeeder) — Plan/Módulo controla qué se ve en el menú,
     * Addon controla qué automatización/feature paga está activa.
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

        // 2. Crear Plan único: Gratis
        DB::table('plans')->updateOrInsert(
            ['slug' => 'free'],
            [
                'name' => 'Gratis',
                'price' => 0.00,
                'duration' => 3650, // 10 años aprox (ilimitado por lógica de negocio)
                'max_users' => 0, // Ilimitado
                'is_default' => true,
                'status' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $freePlanId = DB::table('plans')->where('slug', 'free')->value('id');

        // Desactivar planes de pago legados (goo, essential, business) — ya no se venden así.
        DB::table('plans')->whereIn('slug', ['goo', 'essential', 'business'])->update(['status' => false]);

        // 3. Asignar TODOS los módulos al plan Gratis
        $allModuleIds = DB::table('modules')->pluck('id');
        foreach ($allModuleIds as $moduleId) {
            DB::table('plan_module')->updateOrInsert(
                ['plan_id' => $freePlanId, 'module_id' => $moduleId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Migrar cualquier entidad que estuviera en un plan de pago legado al plan Gratis
        $legacyPlanIds = DB::table('plans')->whereIn('slug', ['goo', 'essential', 'business'])->pluck('id');
        if ($legacyPlanIds->isNotEmpty()) {
            $entitiesOnLegacyPlans = DB::table('entity_plan')
                ->whereIn('plan_id', $legacyPlanIds)
                ->where('status', 'active')
                ->pluck('entity_id');

            foreach ($entitiesOnLegacyPlans as $entityId) {
                DB::table('entity_plan')
                    ->where('entity_id', $entityId)
                    ->whereIn('plan_id', $legacyPlanIds)
                    ->update(['status' => 'cancelled', 'updated_at' => now()]);

                DB::table('entity_plan')->updateOrInsert(
                    ['entity_id' => $entityId, 'plan_id' => $freePlanId],
                    [
                        'start_date' => now(),
                        'end_date' => null,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
