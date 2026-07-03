<?php

namespace App\Http\Modules\Addon\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Entity\Model\Entity;

class Addon extends Model
{
    protected $table = 'addons';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'price',
        'status',
    ];

    public function entidades()
    {
        return $this->belongsToMany(Entity::class, 'entity_addons')
            ->withPivot('status', 'activated_at', 'current_period_end', 'settings')
            ->withTimestamps();
    }
}
