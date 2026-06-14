<?php

namespace App\Http\Modules\Plan\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Module\Model\Module;
use App\Http\Modules\Entity\Model\Entity;

class Plan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'slug',
        'price',
        'duration',
        'max_users',
        'is_default',
        'status',
    ];

    public function modulos()
    {
        return $this->belongsToMany(Module::class, 'plan_module')
            ->withTimestamps();
    }

    public function entidades()
    {
        return $this->belongsToMany(Entity::class, 'entity_plan')
            ->withPivot('start_date', 'end_date', 'status')
            ->withTimestamps();
    }
}
