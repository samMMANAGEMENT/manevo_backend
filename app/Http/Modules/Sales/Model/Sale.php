<?php

namespace App\Http\Modules\Sales\Model;

use App\Models\User;
use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'seller_id',
        'total',
        'total_profit',
        'payment_method',
        'cash_amount',
        'transfer_amount',
        'date',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'date' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(\App\Http\Modules\Billing\Model\InvoiceItem::class, 'invoicable_id')
            ->where('invoicable_type', self::class);
    }
}
