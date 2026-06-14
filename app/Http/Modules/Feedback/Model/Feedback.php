<?php

namespace App\Http\Modules\Feedback\Model;

use Illuminate\Database\Eloquent\Model;
use App\Http\Modules\Entity\Model\Entity;
use App\Models\User;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'entity_id',
        'user_id',
        'type',
        'rating',
        'message'
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
