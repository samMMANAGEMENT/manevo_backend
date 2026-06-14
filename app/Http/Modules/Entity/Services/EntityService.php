<?php

namespace App\Http\Modules\Entity\Services;

use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Plan\Model\Plan;
use Carbon\Carbon;

class EntityService
{
    public function obtenerEntidades()
    {
        return Entity::with('planes.modulos')->get();
    }

    public function obtenerEntidad($id)
    {
        return Entity::with('planes.modulos')->findOrFail($id);
    }

    public function crearEntidad(array $data)
    {
        return Entity::create($data);
    }

    public function modificarEntidad(array $data, $id)
    {
        $entity = Entity::findOrFail($id);
        $entity->update($data);
        return $entity;
    }

    public function eliminarEntidad($id)
    {
        $entity = Entity::findOrFail($id);
        return $entity->delete();
    }

    public function asignarPlan($entityId, $planId, $data = [])
    {
        $entity = Entity::findOrFail($entityId);
        $plan = Plan::findOrFail($planId);

        // Desactivar planes anteriores si es necesario
        // $entity->planes()->updateExistingPivot(['status' => 'past_due']);

        $startDate = Carbon::now();
        $endDate = $plan->duration > 0 ? $startDate->copy()->addDays($plan->duration) : null;

        $entity->planes()->attach($plan->id, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $data['status'] ?? 'active'
        ]);

        return $entity->load('planes.modulos');
    }
}
