<?php

namespace App\Http\Modules\Expense\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Expense extends Model
{
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'category',
        'description',
        'amount',
        'date',
        'payment_method',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
