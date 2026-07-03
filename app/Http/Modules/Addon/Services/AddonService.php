<?php

namespace App\Http\Modules\Addon\Services;

use App\Http\Modules\Addon\Model\Addon;
use App\Http\Modules\Entity\Model\Entity;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AddonService
{
    public function listarAddons()
    {
        return Addon::where('status', true)->get();
    }

    public function addonsDeEntidad(int $entityId)
    {
        $entity = Entity::findOrFail($entityId);

        return $entity->addons()
            ->wherePivot('status', 'active')
            ->get();
    }

    public function tieneAddonActivo(int $entityId, string $slug): bool
    {
        return Cache::remember("entity_addon_{$entityId}_{$slug}", 300, function () use ($entityId, $slug) {
            $addon = Addon::where('slug', $slug)->first();
            if (!$addon) {
                return false;
            }

            $entity = Entity::find($entityId);
            if (!$entity) {
                return false;
            }

            return $entity->addons()
                ->where('addons.id', $addon->id)
                ->wherePivot('status', 'active')
                ->where(function ($query) {
                    $query->whereNull('current_period_end')
                        ->orWhere('current_period_end', '>=', now());
                })
                ->exists();
        });
    }

    public function activarAddon(int $entityId, string $slug, ?Carbon $periodEnd = null)
    {
        $entity = Entity::findOrFail($entityId);
        $addon = Addon::where('slug', $slug)->firstOrFail();

        $entity->addons()->syncWithoutDetaching([
            $addon->id => [
                'status' => 'active',
                'activated_at' => now(),
                'current_period_end' => $periodEnd,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ]);

        $this->limpiarCache($entityId, $slug);

        return $entity->addons()->where('addons.id', $addon->id)->first();
    }

    public function desactivarAddon(int $entityId, string $slug)
    {
        $entity = Entity::findOrFail($entityId);
        $addon = Addon::where('slug', $slug)->firstOrFail();

        $entity->addons()->updateExistingPivot($addon->id, [
            'status' => 'cancelled',
            'updated_at' => now(),
        ]);

        $this->limpiarCache($entityId, $slug);

        return true;
    }

    /**
     * Configuración libre por entidad/addon (ej: horas de anticipación de un recordatorio).
     * Se guarda en el pivot entity_addons.settings como JSON.
     */
    public function obtenerConfiguracion(int $entityId, string $slug): array
    {
        $entity = Entity::find($entityId);
        $addon = Addon::where('slug', $slug)->first();
        if (!$entity || !$addon) {
            return [];
        }

        $pivotRow = $entity->addons()->where('addons.id', $addon->id)->first();
        $settings = $pivotRow?->pivot?->settings;

        return $settings ? json_decode($settings, true) : [];
    }

    public function actualizarConfiguracion(int $entityId, string $slug, array $settings): array
    {
        $entity = Entity::findOrFail($entityId);
        $addon = Addon::where('slug', $slug)->firstOrFail();

        $entity->addons()->updateExistingPivot($addon->id, [
            'settings' => json_encode($settings),
            'updated_at' => now(),
        ]);

        return $settings;
    }

    private function limpiarCache(int $entityId, string $slug): void
    {
        Cache::forget("entity_addon_{$entityId}_{$slug}");
    }
}
