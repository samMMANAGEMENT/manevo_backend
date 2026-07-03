<?php

namespace App\Http\Modules\Addon\Model;

use Illuminate\Database\Eloquent\Model;

class AddonEvent extends Model
{
    protected $table = 'addon_events';

    protected $fillable = [
        'entity_id',
        'addon_id',
        'event_type',
        'reference_type',
        'reference_id',
        'status',
        'external_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
