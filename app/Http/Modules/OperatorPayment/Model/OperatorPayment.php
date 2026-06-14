<?php

namespace App\Http\Modules\OperatorPayment\Model;

use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Operator\Model\Operator;
use App\Http\Modules\Services\Model\ServicePerformance;
use App\Models\User;

class OperatorPayment extends Model
{
    use BelongsToEntity;

    protected $table = 'operator_payments';

    protected $fillable = [
        'entity_id',
        'operator_id',
        'amount',
        'payment_date',
        'payment_method',
        'status',
        'reference',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function operator()
    {
        return $this->belongsTo(Operator::class);
    }

    public function performances()
    {
        return $this->belongsToMany(ServicePerformance::class, 'operator_payment_performances', 'operator_payment_id', 'service_performance_id')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
