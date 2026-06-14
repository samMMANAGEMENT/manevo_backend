<?php

namespace App\Http\Modules\Module\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Plan\Model\Plan;

class Module extends Model
{
    protected $table = 'modules';

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public function planes()
    {
        return $this->belongsToMany(Plan::class, 'plan_module')
            ->withTimestamps();
    }
}
