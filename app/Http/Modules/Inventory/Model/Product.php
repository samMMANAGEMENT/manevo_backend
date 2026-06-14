<?php

namespace App\Http\Modules\Inventory\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'name',
        'quantity',
        'unit_cost',
        'selling_price',
        'status',
        'package_size',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'package_size' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }
}