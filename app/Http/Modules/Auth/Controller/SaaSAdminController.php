<?php

namespace App\Http\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Plan\Model\Plan;
use App\Http\Modules\Addon\Model\Addon;
use App\Http\Modules\Addon\Services\AddonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SaaSAdminController extends Controller
{
    public function __construct(private AddonService $addonService)
    {
    }

    /**
     * Verificar que el usuario sea el superadministrador específico
     */
    private function checkAccess()
    {
        $user = auth()->user();
        if (!$user || $user->email !== 'sapinedal05@outlook.com') {
            abort(403, 'Acceso denegado. Solo el superadministrador (sapinedal05@outlook.com) tiene acceso.');
        }
    }

    /**
     * Listar todos los workspaces (entidades) con sus planes y usuarios asociados
     */
    public function obtenerWorkspacesPlataforma()
    {
        $this->checkAccess();

        $entities = Entity::with(['planes' => function ($query) {
            $query->withPivot('start_date', 'end_date', 'status');
        }, 'addons' => function ($query) {
            $query->withPivot('status', 'activated_at', 'current_period_end');
        }, 'users'])->get();

        $formatted = $entities->map(function ($entity) {
            $activePlan = $entity->planes
                ->where('pivot.status', 'active')
                ->first();

            return [
                'id' => $entity->id,
                'name' => $entity->name,
                'description' => $entity->description,
                'created_at' => $entity->created_at,
                'plan' => $activePlan ? [
                    'id' => $activePlan->id,
                    'name' => $activePlan->name,
                    'slug' => $activePlan->slug,
                    'start_date' => $activePlan->pivot->start_date,
                    'end_date' => $activePlan->pivot->end_date,
                    'status' => $activePlan->pivot->status,
                ] : null,
                'addons' => $entity->addons
                    ->where('pivot.status', 'active')
                    ->map(function ($addon) {
                        return [
                            'id' => $addon->id,
                            'slug' => $addon->slug,
                            'name' => $addon->name,
                            'activated_at' => $addon->pivot->activated_at,
                            'current_period_end' => $addon->pivot->current_period_end,
                        ];
                    })->values(),
                'users' => $entity->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                    ];
                }),
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Obtener todos los addons disponibles en la plataforma
     */
    public function obtenerAddonsSaaS()
    {
        $this->checkAccess();

        return response()->json(Addon::orderBy('name')->get());
    }

    /**
     * Activar un addon para un workspace/entidad (mientras no haya pasarela de pagos, activación manual)
     */
    public function activarAddonEntidad(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'addon_slug' => 'required|exists:addons,slug',
            'current_period_end' => 'nullable|date',
        ]);

        $entityAddon = $this->addonService->activarAddon(
            $validated['entity_id'],
            $validated['addon_slug'],
            isset($validated['current_period_end']) ? Carbon::parse($validated['current_period_end']) : null
        );

        return response()->json([
            'message' => 'Addon activado con éxito',
            'entity_id' => $validated['entity_id'],
            'addon' => $entityAddon,
        ]);
    }

    /**
     * Desactivar un addon para un workspace/entidad
     */
    public function desactivarAddonEntidad(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'addon_slug' => 'required|exists:addons,slug',
        ]);

        $this->addonService->desactivarAddon($validated['entity_id'], $validated['addon_slug']);

        return response()->json([
            'message' => 'Addon desactivado con éxito',
            'entity_id' => $validated['entity_id'],
        ]);
    }

    /**
     * Obtener todos los planes disponibles con sus módulos
     */
    public function obtenerPlanesSaaS()
    {
        $this->checkAccess();

        $planes = Plan::with('modulos')->get();
        return response()->json($planes);
    }

    /**
     * Modificar el plan de un workspace/entidad
     */
    public function modificarPlanEntidad(Request $request)
    {
        $this->checkAccess();

        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'plan_id' => 'required|exists:plans,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $entity = Entity::findOrFail($validated['entity_id']);
            $plan = Plan::findOrFail($validated['plan_id']);

            // Desvincular planes anteriores de la entidad
            $entity->planes()->detach();

            // Calcular fechas de inicio y fin
            $startDate = Carbon::now();
            $endDate = $plan->duration > 0 ? $startDate->copy()->addDays($plan->duration) : null;

            // Vincular nuevo plan
            $entity->planes()->attach($plan->id, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Limpiar caché de Laravel para que los permisos se actualicen inmediatamente
            Cache::forget("entity_plan_{$entity->id}");
            Cache::forget("plan_modules_{$plan->id}");

            // También podemos limpiar la caché de todos los usuarios de esa entidad si se requiere, 
            // pero con limpiar la de la entidad ya se leerá el plan actualizado la próxima vez.
            
            return response()->json([
                'message' => 'Plan actualizado con éxito para la entidad ' . $entity->name,
                'entity_id' => $entity->id,
                'plan' => [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                ]
            ]);
        });
    }
}
