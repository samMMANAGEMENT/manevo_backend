<?php

namespace App\Http\Modules\Entity\Model;

use App\Http\Modules\Entity\Model\Entity;
use App\Traits\BelongsToEntity;
use Illuminate\Database\Eloquent\Model;

class BillingConfiguration extends Model
{
    use BelongsToEntity;

    protected $table = 'billing_configurations';

    protected $fillable = [
        'entity_id',
        'razon_social',
        'document_type',
        'nit',
        'dv',
        'email_billing',
        'phone_billing',
        'address_billing',
        'city_billing',
        'tax_regime',
        'resolution_number',
        'resolution_date',
        'prefix',
        'start_range',
        'end_range',
        'software_id',
        'software_pin',
        'api_token',
        'api_base_url',
        'test_set_id',
        'is_test',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }
}
