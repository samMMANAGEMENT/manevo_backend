<?php

namespace App\Http\Modules\Services\Model;

use App\Http\Modules\Operator\Model\Operator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePerformance extends Model
{
    protected $fillable = [
        'order_id',
        'service_id',
        'operator_id',
        'quantity',
        'price_snapshot',
        'commission_percentage_snapshot',
        'total_gross',
        'discount_percentage',
        'discount_amount',
        'total_net',
        'proportional_cash',
        'proportional_transfer',
        'is_paid_to_employee',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_snapshot' => 'decimal:2',
        'commission_percentage_snapshot' => 'decimal:2',
        'total_gross' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_net' => 'decimal:2',
        'proportional_cash' => 'decimal:2',
        'proportional_transfer' => 'decimal:2',
        'is_paid_to_employee' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'order_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Operator::class, 'operator_id');
    }
}
