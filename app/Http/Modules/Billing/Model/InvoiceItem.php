<?php

namespace App\Http\Modules\Billing\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'invoicable_type',
        'invoicable_id',
        'description',
        'quantity',
        'price',
        'discount',
        'tax_percentage',
        'tax_amount',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoicable(): MorphTo
    {
        return $this->morphTo();
    }
}
