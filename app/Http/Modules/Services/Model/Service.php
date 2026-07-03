<?php

namespace App\Http\Modules\Services\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'name',
        'price',
        'employee_percentage',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'employee_percentage' => 'decimal:2',
        'duration_minutes' => 'integer',
        'status' => 'boolean',
    ];

    public function performances(): HasMany
    {
        return $this->hasMany(ServicePerformance::class);
    }
}
