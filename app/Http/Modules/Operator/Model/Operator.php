<?php

namespace App\Http\Modules\Operator\Model;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Operator extends Model
{
    protected $table = 'operators';

    protected $fillable = [
        'type_document',
        'document',
        'mobile',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
