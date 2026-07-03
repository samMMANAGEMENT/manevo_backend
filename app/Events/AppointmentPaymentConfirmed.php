<?php

namespace App\Events;

use App\Http\Modules\Appointment\Model\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentPaymentConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }
}
