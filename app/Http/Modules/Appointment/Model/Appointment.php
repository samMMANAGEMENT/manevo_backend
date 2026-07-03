<?php

namespace App\Http\Modules\Appointment\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Http\Modules\Entity\Model\Customer;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Operator\Model\Operator;
use App\Http\Modules\Services\Model\Service;
use App\Http\Modules\Addon\Model\AddonEvent;

class Appointment extends Model
{
    use BelongsToEntity;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'entity_id',
        'customer_id',
        'operator_id',
        'service_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'payment_status',
        'price',
        'source',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function reminderEvents(): HasMany
    {
        return $this->hasMany(AddonEvent::class, 'reference_id')
            ->where('reference_type', self::class)
            ->where('event_type', 'reminder_sent');
    }

    public function getEndsAtAttribute()
    {
        return $this->scheduled_at?->copy()->addMinutes($this->duration_minutes);
    }
}
