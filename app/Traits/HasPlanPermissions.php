<?php

namespace App\Traits;

use App\Http\Modules\Entity\Model\Entity;
use Illuminate\Support\Facades\Cache;

trait HasPlanPermissions
{
    /**
     * Obtener el plan activo de la entidad del usuario
     */
    public function getActivePlan()
    {
        // Cachear el plan para no consultar BD cada vez
        return Cache::remember("entity_plan_{$this->entity_id}", 3600, function () {
            $entity = Entity::find($this->entity_id);
            if (!$entity)
                return null;

            return $entity->planes()
                ->wherePivot('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->first();
        });
    }

    /**
     * Obtener los slugs de los módulos habilitados por el plan
     */
    public function getEnabledModules(): array
    {
        $plan = $this->getActivePlan();
        if (!$plan)
            return [];

        return Cache::remember("plan_modules_{$plan->id}", 3600, function () use ($plan) {
            return $plan->modulos()->pluck('slug')->toArray();
        });
    }

    /**
     * Verifica si un usuario tiene un permiso, pero VALIDANDO también su plan
     */
    public function canAccess(string $permission): bool
    {
        // 1. Si es Super Admin, tiene acceso total siempre
        if ($this->roles->pluck('name')->contains('super_admin')) {
            return true;
        }

        // 2. Verificar si tiene el permiso por Spatie (Roles/Directo)
        if (!$this->hasPermissionTo($permission)) {
            return false;
        }

        // 3. Extraer el módulo del nombre del permiso (ej: 'pos.vender' -> 'pos')
        $moduleSlug = explode('.', $permission)[0];

        // 4. Verificar si el módulo está habilitado en su plan
        $enabledModules = $this->getEnabledModules();

        return in_array($moduleSlug, $enabledModules);
    }

    /**
     * Devuelve la lista de todos los permisos filtrados por el plan
     * Esto es útil para enviarlo al Frontend
     */
    public function getFilteredPermissions(): array
    {
        $allPermissions = $this->getAllPermissions()->pluck('name')->toArray();

        // Si es Super Admin, no filtramos por plan
        // Usamos la relación directamente para evitar problemas de guard en el método hasRole()
        if ($this->roles->pluck('name')->contains('super_admin')) {
            return array_values(array_unique($allPermissions));
        }

        $enabledModules = $this->getEnabledModules();

        return array_values(array_filter($allPermissions, function ($permission) use ($enabledModules) {
            $moduleSlug = explode('.', $permission)[0];
            return in_array($moduleSlug, $enabledModules);
        }));
    }
}
