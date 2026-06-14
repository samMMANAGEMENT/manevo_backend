<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToEntity
{
    /**
     * El "boot" del trait se ejecuta automáticamente al usar el modelo.
     */
    protected static function bootBelongsToEntity()
    {
        // 1. FILTRO AUTOMÁTICO (Global Scope)
        // Cada vez que consultes el modelo (ej: Product::all()), 
        // Laravel agregará automáticamente: WHERE entity_id = X
        static::addGlobalScope('entity_scope', function (Builder $builder) {
            if (Auth::check() && Auth::user()->entity_id) {
                // Si el usuario no es Super Admin, filtramos por su entidad
                if (!Auth::user()->hasRole('super_admin')) {
                    $builder->where($builder->getQuery()->from . '.entity_id', Auth::user()->entity_id);
                }
            }
        });

        // 2. ASIGNACIÓN AUTOMÁTICA
        // Al crear un nuevo registro (ej: Product::create([...])), 
        // el entity_id se asigna solo, sin que el programador tenga que enviarlo.
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->entity_id && !$model->entity_id) {
                $model->entity_id = Auth::user()->entity_id;
            }
        });
    }
}
