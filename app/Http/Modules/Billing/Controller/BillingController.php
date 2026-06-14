<?php

namespace App\Http\Modules\Billing\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Billing\Model\Invoice;
use App\Http\Modules\Billing\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index()
    {
        $entityId = Auth::user()->entity_id;
        $invoices = Invoice::where('entity_id', $entityId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invoices);
    }

    public function getPendingItems()
    {
        $items = $this->billingService->getPendingItems();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_identification' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'nullable|string',
            'customer_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'total' => 'required|numeric',
        ]);

        try {
            $invoice = $this->billingService->createInvoice($request->all());

            // Auto send to provider (optional, could be a separate step)
            $this->billingService->sendToDataInvoice($invoice);

            return response()->json([
                'message' => 'Factura creada y enviada con éxito',
                'invoice' => $invoice->load('items')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function sendToProvider(Invoice $invoice)
    {
        if ($invoice->entity_id !== Auth::user()->entity_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->billingService->sendToDataInvoice($invoice);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
