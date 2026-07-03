<?php

namespace App\Http\Modules\Appointment\Services;

use App\Events\AppointmentPaymentConfirmed;
use App\Http\Modules\Appointment\Model\Appointment;
use App\Http\Modules\Services\Model\Service;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

class AppointmentService
{
    // Horario laboral por defecto mientras no exista configuración por entidad/operario.
    private const WORKDAY_START = '09:00';
    private const WORKDAY_END = '19:00';
    private const SLOT_STEP_MINUTES = 30;

    public function listar(array $filters = [])
    {
        $query = Appointment::with(['customer', 'operator.user', 'service']);

        if (!empty($filters['from'])) {
            $query->where('scheduled_at', '>=', Carbon::parse($filters['from']));
        }
        if (!empty($filters['to'])) {
            $query->where('scheduled_at', '<=', Carbon::parse($filters['to']));
        }
        if (!empty($filters['operator_id'])) {
            $query->where('operator_id', $filters['operator_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('scheduled_at')->get();
    }

    public function obtener(int $id)
    {
        return Appointment::with(['customer', 'operator.user', 'service'])->findOrFail($id);
    }

    public function crear(array $data)
    {
        $duration = $data['duration_minutes'] ?? null;
        $price = $data['price'] ?? null;

        if (!empty($data['service_id']) && (!$duration || !$price)) {
            $service = Service::find($data['service_id']);
            if ($service) {
                $duration = $duration ?: $service->duration_minutes;
                $price = $price ?? $service->price;
            }
        }

        return Appointment::create([
            'entity_id' => $data['entity_id'] ?? Auth::user()->entity_id,
            'customer_id' => $data['customer_id'],
            'operator_id' => $data['operator_id'] ?? null,
            'service_id' => $data['service_id'] ?? null,
            'scheduled_at' => Carbon::parse($data['scheduled_at']),
            'duration_minutes' => $duration ?: 60,
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_UNPAID,
            'price' => $price,
            'source' => $data['source'] ?? 'manual',
            'notes' => $data['notes'] ?? null,
        ])->load(['customer', 'operator.user', 'service']);
    }

    public function actualizar(int $id, array $data)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(array_filter([
            'customer_id' => $data['customer_id'] ?? null,
            'operator_id' => $data['operator_id'] ?? null,
            'service_id' => $data['service_id'] ?? null,
            'scheduled_at' => isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'price' => $data['price'] ?? null,
            'notes' => $data['notes'] ?? null,
        ], fn($v) => $v !== null));

        return $appointment->load(['customer', 'operator.user', 'service']);
    }

    public function cambiarEstado(int $id, string $estado)
    {
        $valid = [
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_NO_SHOW,
        ];

        if (!in_array($estado, $valid)) {
            throw new \InvalidArgumentException("Estado inválido: {$estado}");
        }

        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => $estado]);

        return $appointment;
    }

    public function marcarPagada(int $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->payment_status === Appointment::PAYMENT_PAID) {
            return $appointment;
        }

        $appointment->update(['payment_status' => Appointment::PAYMENT_PAID]);

        event(new AppointmentPaymentConfirmed($appointment));

        return $appointment;
    }

    public function eliminar(int $id)
    {
        return Appointment::findOrFail($id)->delete();
    }

    /**
     * Slots disponibles para un operario en una fecha dada.
     * Horario fijo (09:00-19:00) menos citas ya existentes ese día, sin configuración por entidad todavía.
     */
    public function disponibilidad(int $entityId, ?int $operatorId, string $date, int $durationMinutes = 60): array
    {
        $day = Carbon::parse($date)->startOfDay();
        $windowStart = $day->copy()->setTimeFromTimeString(self::WORKDAY_START);
        $windowEnd = $day->copy()->setTimeFromTimeString(self::WORKDAY_END);

        $query = Appointment::where('entity_id', $entityId)
            ->whereDate('scheduled_at', $day)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW]);

        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        $busy = $query->get(['scheduled_at', 'duration_minutes'])->map(function ($appt) {
            return [
                'start' => $appt->scheduled_at,
                'end' => $appt->scheduled_at->copy()->addMinutes($appt->duration_minutes),
            ];
        });

        $slots = [];
        $period = CarbonPeriod::create($windowStart, self::SLOT_STEP_MINUTES . ' minutes', $windowEnd->copy()->subMinutes($durationMinutes));

        foreach ($period as $slotStart) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);

            $overlaps = $busy->contains(function ($b) use ($slotStart, $slotEnd) {
                return $slotStart < $b['end'] && $slotEnd > $b['start'];
            });

            if (!$overlaps && $slotStart->isFuture()) {
                $slots[] = $slotStart->format('H:i');
            }
        }

        return $slots;
    }
}
