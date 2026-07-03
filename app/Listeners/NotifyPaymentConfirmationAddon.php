<?php

namespace App\Listeners;

use App\Events\AppointmentPaymentConfirmed;
use App\Http\Modules\Addon\Services\AddonService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotifyPaymentConfirmationAddon implements ShouldQueue
{
    public function __construct(private AddonService $addonService)
    {
    }

    public function handle(AppointmentPaymentConfirmed $event): void
    {
        $appointment = $event->appointment;

        if (!$this->addonService->tieneAddonActivo($appointment->entity_id, 'confirmacion-pago')) {
            return;
        }

        $webhookUrl = config('services.n8n.webhook_payment_confirmed');
        if (!$webhookUrl) {
            Log::warning('N8N_WEBHOOK_PAYMENT_CONFIRMED no configurado, no se pudo notificar confirmación de pago.', [
                'appointment_id' => $appointment->id,
            ]);
            return;
        }

        $appointment->loadMissing(['customer', 'service']);

        try {
            Http::timeout(10)->post($webhookUrl, [
                'entity_id' => $appointment->entity_id,
                'appointment_id' => $appointment->id,
                'customer_name' => $appointment->customer->name,
                'customer_phone' => $appointment->customer->phone,
                'service_name' => $appointment->service->name ?? null,
                'price' => $appointment->price,
                'scheduled_at' => $appointment->scheduled_at->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error notificando confirmación de pago a n8n: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
            ]);
        }
    }
}
