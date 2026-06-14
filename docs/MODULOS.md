# Guía de Creación de Módulos - manevo

Para añadir un nuevo módulo al sistema (ej. **Inventario**), debes seguir estos pasos para asegurar que la integración con el sistema de planes, permisos y frontend sea correcta.

---

## 1. Registro en Base de Datos (Backend)

Todos los módulos deben existir en la tabla `modules` y estar vinculados a un plan en `plan_module`.

### Paso A: Actualizar `PlanAndModuleSeeder.php`
Edita `database/seeders/PlanAndModuleSeeder.php`:
1. Añade el módulo al array `$modules`:
   ```php
   ['name' => 'Inventario', 'slug' => 'inventory', 'created_at' => now(), 'updated_at' => now()],
   ```
2. Asígnalo a los planes correspondientes dentro del `foreach ($allModules ...)`:
   ```php
   if ($module->slug === 'inventory') {
       $this->assignToPlans($module->id, [$gooPlanId, $essentialPlanId, $businessPlanId]);
   }
   ```

---

## 2. Configuración de Permisos (Backend)

El sistema de permisos es dinámico y depende de Spatie Permissions.

### Paso B: Actualizar `PermissionSeeder.php`
Edita `database/seeders/PermissionSeeder.php`:
1. Define los permisos base y específicos:
   ```php
   if ($module->slug === 'inventory') {
       $permissions[] = ['name' => 'inventory.menu', 'description' => 'Acceso al menú de Inventario', 'guard_name' => 'api', ...];
       $permissions[] = ['name' => 'inventory.crear', 'description' => 'Crear productos', 'guard_name' => 'api', ...];
   }
   ```
2. Asígnalos a los roles (Operator, Admin, etc.) si es necesario. El `super_admin` los tendrá automáticamente gracias a la lógica de `syncPermissions(Permission::all())`.

---

## 3. Estructura de Código (Backend)

Sigue el patrón modular de la aplicación:
1. Crea la carpeta: `app/Http/Modules/Inventory/`
2. Subcarpetas recomendadas:
   - `Controller/`: Controladores API.
   - `Model/`: Modelos Eloquent.
   - `Services/`: Lógica de negocio.
   - `Requests/`: Validaciones.

---

## 4. Integración en el Frontend

### Paso C: Sidebar y Navegación
Edita `src/shared/components/layout/Sidebar.tsx`:
1. Añade el ítem al array `principalItems` o `avanzadoItems`:
   ```typescript
   { name: 'Inventario', slug: 'inventory', icon: 'inventory', perm: 'inventory.menu' },
   ```

### Paso D: Rutas
Edita `src/router/router.tsx`:
1. Registra la ruta dentro del grupo de `AppLayout`:
   ```typescript
   { path: 'inventory', element: <InventoryPage /> },
   ```

### Paso E: Estructura de Carpetas
Crea la carpeta `src/modules/inventory/` para tus componentes, páginas y servicios específicos del módulo.

---

## 5. Aplicar Cambios

Una vez editados los seeders, ejecuta:
```bash
php artisan migrate:fresh --seed
```
*Nota: Esto borrará los datos actuales. Si solo quieres actualizar permisos/módulos sin borrar todo, puedes ejecutar los seeders específicos:*
```bash
php artisan db:seed --class=PlanAndModuleSeeder
php artisan db:seed --class=PermissionSeeder
```
