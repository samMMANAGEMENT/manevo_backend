<?php

namespace App\Http\Modules\OperatorPayment\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\OperatorPayment\Services\OperatorPaymentService;
use App\Http\Modules\OperatorPayment\Model\OperatorPayment;
use Illuminate\Http\Request;

class OperatorPaymentController extends Controller
{
    public function __construct(private OperatorPaymentService $service)
    {
    }

    /**
     * Get list of all operator payments for the authenticated user's workspace.
     */
    public function index()
    {
        try {
            $payments = $this->service->getAll();
            return response()->json($payments, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Get list of pending (unpaid) service commissions for the authenticated user's workspace.
     */
    public function pendingCommissions(Request $request)
    {
        try {
            $operatorId = $request->query('operator_id');
            $commissions = $this->service->getPendingCommissions($operatorId);
            return response()->json($commissions, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Create/record a new operator payout.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'operator_id' => 'required|exists:operators,id',
                'amount' => 'required|numeric|min:0',
                'payment_date' => 'required|date',
                'payment_method' => 'required|string',
                'status' => 'required|string|in:Paid,Pending',
                'reference' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'performance_ids' => 'nullable|array',
                'performance_ids.*' => 'exists:service_performances,id',
            ]);

            $payment = $this->service->create($validated);
            return response()->json($payment, 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    /**
     * Delete/revert an operator payout.
     */
    public function destroy(OperatorPayment $payment)
    {
        try {
            $this->service->delete($payment);
            return response()->json(null, 204);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
