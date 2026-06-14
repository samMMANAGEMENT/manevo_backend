<?php

namespace App\Http\Modules\Billing\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'number',
        'prefix',
        'cufe',
        'status',
        'customer_name',
        'customer_identification',
        'customer_email',
        'customer_phone',
        'customer_address',
        'subtotal',
        'tax_amount',
        'total',
        'external_id',
        'pdf_url',
        'xml_url',
        'provider_response',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'provider_response' => 'array',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
