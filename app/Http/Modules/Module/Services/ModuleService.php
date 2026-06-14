<?php

namespace App\Http\Modules\Module\Services;

use App\Http\Modules\Module\Model\Module;

class ModuleService
{
    public function obtenerModulos()
    {
        return Module::all();
    }

    public function obtenerModulo($id)
    {
        return Module::findOrFail($id);
    }

    public function crearModulo(array $data)
    {
        return Module::create($data);
    }

    public function modificarModulo(array $data, $id)
    {
        $module = Module::findOrFail($id);
        $module->update($data);
        return $module;
    }

    public function eliminarModulo($id)
    {
        $module = Module::findOrFail($id);
        return $module->delete();
    }
}
