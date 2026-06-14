<?php

namespace App\Http\Modules\Inventory\Model;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'previous_quantity',
        'movement_quantity',
        'new_quantity',
        'date',
    ];

    protected $casts = [
        'previous_quantity' => 'integer',
        'movement_quantity' => 'integer',
        'new_quantity' => 'integer',
        'date' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
