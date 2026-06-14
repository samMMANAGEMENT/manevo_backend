<?php

namespace App\Http\Modules\Services\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'total_net',
        'payment_method',
        'cash_amount',
        'transfer_amount',
        'transaction_token',
        'date',
    ];

    protected $casts = [
        'total_net' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ServicePerformance::class, 'order_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Http\Modules\Billing\Model\InvoiceItem::class, 'invoicable_id')
            ->where('invoicable_type', self::class);
    }
}
