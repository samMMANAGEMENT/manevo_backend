<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $addons = [
            [
                'slug' => 'facturacion-dian',
                'name' => 'Facturación Electrónica DIAN',
                'description' => 'Factura electrónica con cumplimiento DIAN integrado.',
                'price' => 29900.00,
                'status' => true,
            ],
            [
                'slug' => 'recordatorios-citas',
                'name' => 'Recordatorios de Citas',
                'description' => 'WhatsApp automático antes de cada cita para reducir inasistencias.',
                'price' => 39900.00,
                'status' => true,
            ],
            [
                'slug' => 'agendamiento-automatico',
                'name' => 'Agendamiento Automático',
                'description' => 'Bot de WhatsApp que agenda citas solo, sin intervención humana.',
                'price' => 79900.00,
                'status' => true,
            ],
            [
                'slug' => 'confirmacion-pago',
                'name' => 'Confirmación de Pago',
                'description' => 'Notificación automática por WhatsApp cuando se paga una cita.',
                'price' => 39900.00,
                'status' => true,
            ],
            [
                'slug' => 'automatizaciones-pro',
                'name' => 'Automatizaciones Pro',
                'description' => 'Bundle: Recordatorios + Agendamiento Automático + Confirmación de Pago.',
                'price' => 129900.00,
                'status' => true,
            ],
        ];

        foreach ($addons as $addon) {
            DB::table('addons')->updateOrInsert(
                ['slug' => $addon['slug']],
                array_merge($addon, ['updated_at' => now(), 'created_at' => now()])
            );
        }
    }
}
