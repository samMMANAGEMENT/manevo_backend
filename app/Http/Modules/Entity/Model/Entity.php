<?php

namespace App\Http\Modules\Entity\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Plan\Model\Plan;
use App\Http\Modules\Entity\Model\Customer;

class Entity extends Model
{
    protected $table = 'entities';

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'entity_plan')
            ->withPivot('start_date', 'end_date', 'status')
            ->withTimestamps();
    }

    protected static function booted()
    {
        static::created(function ($entity) {
            // Crear automáticamente el Consumidor Final (2222) para cada nueva entidad
            Customer::create([
                'entity_id' => $entity->id,
                'name' => 'CONSUMIDOR FINAL',
                'identification_type' => 'CC',
                'identification_number' => '2222',
                'email' => 'consumidor@final.com',
                'municipality_id' => 822,
                'type_regime_id' => 2,
                'type_organization_id' => 2,
                'status' => true,
            ]);
        });
    }
}
