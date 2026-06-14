<?php

namespace App\Http\Modules\Entity\Services;

use App\Http\Modules\Entity\Model\Customer;

class CustomerService
{
    /**
     * Obtiene el listado de clientes de la entidad actual.
     * El filtro por entity_id se aplica mediante el trait BelongsToEntity.
     */
    public function obtenerClientes()
    {
        return Customer::orderBy('name', 'asc')->get();
    }

    /**
     * Crea un nuevo cliente en la entidad.
     */
    public function crearCliente(array $data)
    {
        return Customer::create($data);
    }

    /**
     * Actualiza la información de un cliente existente.
     */
    public function modificarCliente($id, array $data)
    {
        $cliente = Customer::findOrFail($id);
        $cliente->update($data);
        return $cliente;
    }

    /**
     * Elimina un cliente.
     */
    public function eliminarCliente($id)
    {
        $cliente = Customer::findOrFail($id);
        $cliente->delete();
        return true;
    }
}
