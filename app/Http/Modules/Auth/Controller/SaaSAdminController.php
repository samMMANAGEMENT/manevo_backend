<?php

namespace App\Http\Modules\Auth\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Plan\Model\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SaaSAdminController extends Controller
{
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
     * Listar todos los usuarios del sistema con sus entidades y planes activos
     */
    public function obtenerUsuariosPlataforma()
    {
        $this->checkAccess();

        $users = User::with(['entity.planes' => function ($query) {
            $query->withPivot('start_date', 'end_date', 'status');
        }])->get();

        $formattedUsers = $users->map(function ($user) {
            $activePlan = null;
            if ($user->entity && $user->entity->planes) {
                $activePlan = $user->entity->planes
                    ->where('pivot.status', 'active')
                    ->first();
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'entity' => $user->entity ? [
                    'id' => $user->entity->id,
                    'name' => $user->entity->name,
                    'description' => $user->entity->description,
                ] : null,
                'plan' => $activePlan ? [
                    'id' => $activePlan->id,
                    'name' => $activePlan->name,
                    'slug' => $activePlan->slug,
                    'start_date' => $activePlan->pivot->start_date,
                    'end_date' => $activePlan->pivot->end_date,
                    'status' => $activePlan->pivot->status,
                ] : null,
            ];
        });

        return response()->json($formattedUsers);
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
