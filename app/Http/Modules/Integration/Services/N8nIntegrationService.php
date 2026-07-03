<?php

namespace App\Http\Modules\Integration\Services;

use App\Http\Modules\Addon\Model\Addon;
use App\Http\Modules\Addon\Model\AddonEvent;
use App\Http\Modules\Addon\Services\AddonService;
use App\Http\Modules\Appointment\Model\Appointment;
use App\Http\Modules\Appointment\Services\AppointmentService;
use App\Http\Modules\Entity\Model\Entity;
use App\Http\Modules\Services\Model\Service;
use Carbon\Carbon;

/**
 * Toda consulta aquí filtra explícitamente por entity_id — estas llamadas
 * no pasan por Auth::user(), así que el scope automático de BelongsToEntity
 * NO se aplica (ver App\Traits\BelongsToEntity).
 */
class N8nIntegrationService
{
    // Horas de anticipación por defecto si la entidad no ha configurado nada.
    private const DEFAULT_REMINDER_HOURS_BEFORE = 24;

    // Horizonte máximo de búsqueda (debe cubrir el max permitido en AddonController::actualizarConfiguracion, 168h).
    private const MAX_SEARCH_HORIZON_HOURS = 168;

    public function __construct(
        private AddonService $addonService,
        private AppointmentService $appointmentService
    ) {
    }

    public function estadoAddon(int $entityId, string $slug): array
    {
        $entity = Entity::findOrFail($entityId);

        return [
            'entity_id' => $entity->id,
            'business_name' => $entity->name,
            'addon' => $slug,
            'active' => $this->addonService->tieneAddonActivo($entityId, $slug),
        ];
    }

    /**
     * Cada entidad define desde la app cuántas horas antes quiere que se avise (Settings > Addons).
     * Aquí se trae un horizonte amplio de citas candidatas y se filtra por la anticipación
     * configurada de cada entidad — así n8n no necesita saber nada de esa configuración,
     * solo llama a este endpoint y confía en lo que devuelve.
     */
    public function recordatoriosPendientes()
    {
        $horizonte = Carbon::now()->addHours(self::MAX_SEARCH_HORIZON_HOURS);

        $citas = Appointment::withoutGlobalScopes()
            ->with(['customer', 'service', 'entity'])
            ->where('scheduled_at', '>', now())
            ->where('scheduled_at', '<=', $horizonte)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW, Appointment::STATUS_COMPLETED])
            ->whereDoesntHave('reminderEvents')
            ->get();

        return $citas->filter(function ($cita) {
            if (!$this->addonService->tieneAddonActivo($cita->entity_id, 'recordatorios-citas')) {
                return false;
            }

            $config = $this->addonService->obtenerConfiguracion($cita->entity_id, 'recordatorios-citas');
            $horasAnticipacion = $config['hours_before'] ?? self::DEFAULT_REMINDER_HOURS_BEFORE;

            return $cita->scheduled_at->lte(now()->addHours($horasAnticipacion));
        })->map(function ($cita) {
            return [
                'appointment_id' => $cita->id,
                'entity_id' => $cita->entity_id,
                'business_name' => $cita->entity->name ?? null,
                'customer_name' => $cita->customer->name,
                'customer_phone' => $cita->customer->phone,
                'service_name' => $cita->service->name ?? null,
                'scheduled_at' => $cita->scheduled_at->toIso8601String(),
            ];
        })->values();
    }

    public function marcarRecordatorioEnviado(int $appointmentId, string $status = 'sent', ?string $externalId = null): void
    {
        $this->registrarAddonEvent($appointmentId, 'recordatorios-citas', 'reminder_sent', $status, $externalId);
    }

    public function serviciosDeEntidad(int $entityId)
    {
        return Service::withoutGlobalScopes()
            ->where('entity_id', $entityId)
            ->where('status', true)
            ->get(['id', 'name', 'price', 'duration_minutes']);
    }

    public function disponibilidad(int $entityId, ?int $operatorId, string $date, int $durationMinutes = 60): array
    {
        return $this->appointmentService->disponibilidad($entityId, $operatorId, $date, $durationMinutes);
    }

    public function crearCitaDesdeBot(int $entityId, array $data)
    {
        return $this->appointmentService->crear(array_merge($data, [
            'entity_id' => $entityId,
            'source' => 'whatsapp_bot',
        ]));
    }

    public function marcarPagoNotificado(int $appointmentId, string $status = 'sent', ?string $externalId = null): void
    {
        $this->registrarAddonEvent($appointmentId, 'confirmacion-pago', 'payment_notified', $status, $externalId);
    }

    private function registrarAddonEvent(int $appointmentId, string $addonSlug, string $eventType, string $status, ?string $externalId): void
    {
        $cita = Appointment::withoutGlobalScopes()->findOrFail($appointmentId);
        $addon = Addon::where('slug', $addonSlug)->firstOrFail();

        AddonEvent::updateOrCreate(
            [
                'addon_id' => $addon->id,
                'entity_id' => $cita->entity_id,
                'reference_type' => Appointment::class,
                'reference_id' => $cita->id,
                'event_type' => $eventType,
            ],
            [
                'status' => $status,
                'external_id' => $externalId,
            ]
        );
    }
}
