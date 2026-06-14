<?php

namespace App\Http\Modules\Services\Services;

use App\Http\Modules\Services\Model\Service;
use App\Http\Modules\Services\Model\ServiceOrder;
use App\Http\Modules\Services\Model\ServicePerformance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class ServicesModuleService
{
    /**
     * List master services for the entity
     */
    public function listServices()
    {
        return Service::where('status', true)->get();
    }

    /**
     * Create a master service
     */
    public function createService(array $data)
    {
        return Service::create($data);
    }

    /**
     * Process multiple service performances in a single order
     */
    public function processServicesOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = Auth::user();
            $items = $data['items'];

            $totalOrderNet = 0;
            foreach ($items as $item) {
                // El total neto de cada item ya viene calculado o validado del front
                // pero lo recalculamos para seguridad base
                $service = Service::findOrFail($item['service_id']);
                $subtotalGross = $service->price * $item['quantity'];
                $discountAmount = ($item['discount_percentage'] / 100) * $subtotalGross;
                $totalOrderNet += ($subtotalGross - $discountAmount);
            }

            // Crear cabecera
            $order = ServiceOrder::create([
                'entity_id' => $user->entity_id,
                'total_net' => $totalOrderNet,
                'payment_method' => $data['payment_method'],
                'cash_amount' => $data['cash_amount'] ?? ($data['payment_method'] === 'cash' ? $totalOrderNet : 0),
                'transfer_amount' => $data['transfer_amount'] ?? ($data['payment_method'] === 'transfer' ? $totalOrderNet : 0),
                'transaction_token' => $data['transaction_token'] ?? null,
                'date' => now(),
            ]);

            $totalItems = count($items);
            $cumulativeCash = 0;
            $cumulativeTransfer = 0;

            foreach ($items as $index => $itemData) {
                $service = Service::findOrFail($itemData['service_id']);

                $gross = $service->price * $itemData['quantity'];
                $discount = ($itemData['discount_percentage'] / 100) * $gross;
                $net = $gross - $discount;

                // Distribución Proporcional
                $proportion = $net / $totalOrderNet;

                if ($index === $totalItems - 1) {
                    // Último ítem: Ajuste de redondeo (Total - Acumulado)
                    $propCash = $order->cash_amount - $cumulativeCash;
                    $propTransfer = $order->transfer_amount - $cumulativeTransfer;
                } else {
                    $propCash = round($order->cash_amount * $proportion, 2);
                    $propTransfer = round($order->transfer_amount * $proportion, 2);

                    $cumulativeCash += $propCash;
                    $cumulativeTransfer += $propTransfer;
                }

                ServicePerformance::create([
                    'order_id' => $order->id,
                    'service_id' => $service->id,
                    'operator_id' => $itemData['operator_id'],
                    'quantity' => $itemData['quantity'],
                    'price_snapshot' => $service->price,
                    'commission_percentage_snapshot' => $service->employee_percentage,
                    'total_gross' => $gross,
                    'discount_percentage' => $itemData['discount_percentage'],
                    'discount_amount' => $discount,
                    'total_net' => $net,
                    'proportional_cash' => $propCash,
                    'proportional_transfer' => $propTransfer,
                    'is_paid_to_employee' => false,
                ]);
            }

            return $order->load('items.service', 'items.employee');
        });
    }

    public function getPerformanceHistory($entityId)
    {
        return ServiceOrder::with(['items.service', 'items.employee'])
            ->where('entity_id', $entityId)
            ->orderBy('date', 'desc')
            ->get();
    }
}
