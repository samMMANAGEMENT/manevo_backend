<?php

namespace App\Http\Modules\Entity\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'name',
        'identification_type',
        'identification_number',
        'dv',
        'email',
        'phone',
        'address',
        'municipality_id',
        'type_regime_id',
        'type_organization_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'municipality_id' => 'integer',
        'type_regime_id' => 'integer',
        'type_organization_id' => 'integer',
    ];
}
