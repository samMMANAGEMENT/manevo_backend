<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definir Permisos base por Módulo (Siguiendo la estructura del Sidebar)
        // La convención es {slug}.menu para el acceso al menú y {slug}.{accion} para acciones internas
        $modules = DB::table('modules')->get();

        $permissions = [];

        foreach ($modules as $module) {
            // Permiso para ver el menú del módulo
            $permissions[] = [
                'name' => "{$module->slug}.menu",
                'description' => "Acceso al menú de {$module->name}",
                'guard_name' => 'api',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Permisos específicos por módulo (Ejemplos)
            if ($module->slug === 'services') {
                $permissions[] = ['name' => 'services.registrar', 'description' => 'Registrar nuevos servicios', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
                $permissions[] = ['name' => 'services.editar', 'description' => 'Editar servicios existentes', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
            }

            if ($module->slug === 'pos') {
                $permissions[] = ['name' => 'pos.vender', 'description' => 'Realizar ventas en POS', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
                $permissions[] = ['name' => 'pos.cortes', 'description' => 'Realizar cortes de caja', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
            }

            if ($module->slug === 'inventory') {
                $permissions[] = ['name' => 'inventory.crear', 'description' => 'Crear productos en inventario', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
                $permissions[] = ['name' => 'inventory.editar', 'description' => 'Editar productos del inventario', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
            }

            if ($module->slug === 'billing') {
                $permissions[] = ['name' => 'billing.crear', 'description' => 'Generar facturas electrónicas', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
                $permissions[] = ['name' => 'billing.lista', 'description' => 'Ver historial de facturas', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
                $permissions[] = ['name' => 'billing.config', 'description' => 'Configurar parámetros de la DIAN', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
            }

            if ($module->slug === 'payments') {
                $permissions[] = ['name' => 'payments.crear', 'description' => 'Registrar pagos a operadores', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
                $permissions[] = ['name' => 'payments.eliminar', 'description' => 'Eliminar registros de pagos', 'guard_name' => 'api', 'created_at' => now(), 'updated_at' => now()];
            }

            // ... Puedes agregar más según necesites
        }

        // Insertar permisos
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission['name'], $permission['guard_name']);
        }

        // Registrar permiso especial de cambio de entidad
        Permission::findOrCreate('change.entity', 'api');

        // 2. Crear Roles y asignar permisos

        // Super Admin: Todo
        $superAdmin = Role::findOrCreate('super_admin', 'api');
        $superAdmin->syncPermissions(Permission::all());

        // Admin: Casi todo (pero limitado a su entidad)
        $admin = Role::findOrCreate('admin', 'api');
        $admin->syncPermissions(Permission::all());

        // Operador: Solo ventas y servicios
        $operator = Role::findOrCreate('operator', 'api');
        $operator->syncPermissions([
            'dashboard.menu',
            'pos.menu',
            'pos.vender',
            'services.menu',
            'services.registrar',
            'schedules.menu'
        ]);
        // 3. Asignar Super Admin a usuarios específicos
        $superAdminUsers = User::whereIn('id', [1, 5])->get();
        foreach ($superAdminUsers as $user) {
            $user->assignRole($superAdmin);
        }
    }
}
