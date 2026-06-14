<?php

namespace App\Http\Modules\Operator\Services;

use App\Http\Modules\Operator\Model\Operator;

class OperatorService
{
    public function obtenerOperadores()
    {
        return Operator::with('user')->get();
    }

    public function obtenerOperador($id)
    {
        return Operator::with('user')->findOrFail($id);
    }

    public function crearOperador(array $data)
    {
        return Operator::create($data);
    }

    public function modificarOperador(array $data, $id)
    {
        $operator = Operator::findOrFail($id);
        $operator->update($data);
        return $operator;
    }

    public function eliminarOperador($id)
    {
        $operator = Operator::findOrFail($id);
        return $operator->delete();
    }
}
