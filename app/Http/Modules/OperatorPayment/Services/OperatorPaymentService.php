<?php

namespace App\Http\Modules\OperatorPayment\Services;

use App\Http\Modules\OperatorPayment\Model\OperatorPayment;
use App\Http\Modules\Services\Model\ServicePerformance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OperatorPaymentService
{
    /**
     * Get all operator payments for the current workspace.
     */
    public function getAll()
    {
        return OperatorPayment::with(['operator.user', 'user:id,name', 'performances.service'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get pending (unpaid) service commissions for the operators in the current workspace.
     */
    public function getPendingCommissions($operatorId = null)
    {
        $entityId = Auth::user()->entity_id;

        $query = ServicePerformance::with(['service', 'order'])
            ->whereHas('order', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            })
            ->where('is_paid_to_employee', false);

        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        return $query->orderBy('created_at', 'desc')->get()->map(function ($perf) {
            // Calculate employee commission based on percentage snapshot and net amount
            $perf->commission_amount = round($perf->total_net * ($perf->commission_percentage_snapshot / 100), 2);
            return $perf;
        });
    }

    /**
     * Create an operator payment and optionally associate it with completed services.
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create payment header
            $payment = OperatorPayment::create([
                'operator_id' => $data['operator_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'] ?? 'Paid',
                'reference' => $data['reference'] ?? null,
                'description' => $data['description'] ?? null,
                'user_id' => Auth::id(),
            ]);

            // If there are specific service performances linked to this payment, save and mark them
            if (!empty($data['performance_ids'])) {
                $payment->performances()->sync($data['performance_ids']);

                // Mark performances as paid to employee
                ServicePerformance::whereIn('id', $data['performance_ids'])
                    ->update(['is_paid_to_employee' => true]);
            }

            return $payment->load(['operator.user', 'performances.service']);
        });
    }

    /**
     * Delete an operator payment and mark associated services back to unpaid.
     */
    public function delete(OperatorPayment $payment)
    {
        return DB::transaction(function () use ($payment) {
            $performanceIds = $payment->performances()->pluck('service_performances.id')->toArray();
            
            if (!empty($performanceIds)) {
                // Revert performances to unpaid status
                ServicePerformance::whereIn('id', $performanceIds)
                    ->update(['is_paid_to_employee' => false]);
            }

            return $payment->delete();
        });
    }
}
