<?php

namespace App\Http\Modules\Plan\Services;

use App\Http\Modules\Plan\Model\Plan;

class PlanService
{
    public function obtenerPlanes()
    {
        return Plan::with('modulos')->get();
    }

    public function obtenerPlan($id)
    {
        return Plan::with('modulos')->findOrFail($id);
    }

    public function crearPlan(array $data)
    {
        $plan = Plan::create($data);
        if (isset($data['modulos'])) {
            $plan->modulos()->sync($data['modulos']);
        }
        return $plan->load('modulos');
    }

    public function modificarPlan(array $data, $id)
    {
        $plan = Plan::findOrFail($id);
        $plan->update($data);
        if (isset($data['modulos'])) {
            $plan->modulos()->sync($data['modulos']);
        }
        return $plan->load('modulos');
    }

    public function eliminarPlan($id)
    {
        $plan = Plan::findOrFail($id);
        return $plan->delete();
    }

    public function sincronizarModulos($id, array $moduloIds)
    {
        $plan = Plan::findOrFail($id);
        $plan->modulos()->sync($moduloIds);
        return $plan->load('modulos');
    }
}
