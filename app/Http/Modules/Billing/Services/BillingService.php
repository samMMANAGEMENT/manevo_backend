<?php

namespace App\Http\Modules\Billing\Services;

use App\Http\Modules\Billing\Model\Invoice;
use App\Http\Modules\Billing\Model\InvoiceItem;
use App\Http\Modules\Entity\Model\BillingConfiguration;
use App\Http\Modules\Sales\Model\Sale;
use App\Http\Modules\Services\Model\ServiceOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BillingService
{
    /**
     * Get Sales and ServiceOrders that haven't been invoiced yet.
     * Uses BelongsToEntity trait for automatic scoping.
     */
    public function getPendingItems()
    {
        $sales = Sale::whereDoesntHave('invoiceItems')
            ->with('items.product')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'type' => 'sale',
                    'date' => $sale->date,
                    'total' => $sale->total,
                    'description' => "Venta POS #$sale->id",
                    'details' => $sale->items->map(fn($i) => $i->product->name . " (x$i->quantity)")->implode(', ')
                ];
            });

        $services = ServiceOrder::whereDoesntHave('invoiceItems')
            ->with('items.service')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'type' => 'service',
                    'date' => $order->date,
                    'total' => $order->total_net,
                    'description' => "Orden de Servicio #$order->id",
                    'details' => $order->items->map(fn($i) => $i->service->name)->implode(', ')
                ];
            });

        return $sales->concat($services)->sortByDesc('date')->values();
    }

    /**
     * Create a new invoice grouping selected items.
     * Uses BelongsToEntity trait for automatic entity_id assignment and scoping.
     */
    public function createInvoice(array $data)
    {
        $config = BillingConfiguration::first();
        if (!$config)
            throw new \Exception("No hay configuración de facturación para esta entidad.");

        // Calculate next number within resolution range
        $lastNumber = Invoice::max('number');
        $nextSequence = $lastNumber ? (int) $lastNumber + 1 : (int) $config->start_range;

        if ($nextSequence > (int) $config->end_range) {
            throw new \Exception("Se ha alcanzado el límite de la resolución DIAN.");
        }

        $invoice = Invoice::create([
            'number' => $nextSequence,
            'prefix' => $config->prefix,
            'status' => 'draft',
            'customer_name' => $data['customer_name'] ?? $data['name'],
            'customer_identification' => $data['customer_identification'] ?? $data['identification'],
            'customer_email' => $data['customer_email'] ?? $data['email'],
            'customer_phone' => $data['customer_phone'] ?? $data['phone'] ?? '3000000000',
            'customer_address' => $data['customer_address'] ?? $data['address'] ?? 'CLL 1 # 1-1',
            'subtotal' => $data['total'],
            'total' => $data['total'],
        ]);

        foreach ($data['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'invoicable_type' => $item['type'] === 'sale' ? Sale::class : ServiceOrder::class,
                'invoicable_id' => $item['id'],
                'description' => $item['description'],
                'quantity' => 1,
                'price' => $item['total'],
                'total' => $item['total'],
            ]);
        }

        return $invoice;
    }

    /**
     * Send invoice data to DataInvoice API.
     */
    public function sendToDataInvoice(Invoice $invoice)
    {
        $config = BillingConfiguration::first();
        if (!$config || !$config->api_token)
            throw new \Exception("Configuración de API incompleta.");

        $payload = [
            'number' => (int) $invoice->number,
            'type_document_id' => 1, // Factura Electrónica
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'resolution_number' => $config->resolution_number,
            'prefix' => $config->prefix,
            'sendmail' => true,
            'customer' => [
                'identification_number' => $invoice->customer_identification,
                'dv' => 0,
                'name' => $invoice->customer_name,
                'phone' => $invoice->customer_phone ?: '3000000000',
                'address' => $invoice->customer_address ?: 'CLL 1 # 1-1',
                'email' => $invoice->customer_email,
                'merchant_registration' => '0000000-00',
                'type_document_identification_id' => 3, // CC default
                'type_organization_id' => 2, // Persona Natural
                'municipality_id' => 822, // Bogotá
                'type_regime_id' => 1, // Responsable de IVA
            ],
            'payment_form' => [
                'payment_form_id' => 1, // Contado
                'payment_method_id' => 10, // Efectivo
            ],
            'legal_monetary_totals' => [
                'line_extension_amount' => number_format($invoice->total, 2, '.', ''),
                'tax_exclusive_amount' => '0.00',
                'tax_inclusive_amount' => number_format($invoice->total, 2, '.', ''),
                'allowance_total_amount' => '0.00',
                'charge_total_amount' => '0.00',
                'payable_amount' => number_format($invoice->total, 2, '.', ''),
            ],
            'invoice_lines' => $invoice->items->map(function ($item) {
                return [
                    'unit_measure_id' => 70, // Unidad
                    'invoiced_quantity' => "1",
                    'line_extension_amount' => number_format($item->total, 2, '.', ''),
                    'free_of_charge_indicator' => false,
                    'description' => $item->description,
                    'code' => 'SERV-01',
                    'type_item_identification_id' => 4,
                    'price_amount' => number_format($item->price, 2, '.', ''),
                    'base_quantity' => "1",
                ];
            })->toArray()
        ];

        try {
            $response = Http::withToken($config->api_token)
                ->post($config->api_base_url . '/ubl2.1/invoice', $payload);

            if ($response->successful()) {
                $resData = $response->json();
                $invoice->update([
                    'status' => 'sent',
                    'cufe' => $resData['cufe'] ?? null,
                    'external_id' => $resData['message'] ?? null,
                    'pdf_url' => $resData['urlinvoicepdf'] ?? null,
                    'xml_url' => $resData['urlinvoicexml'] ?? null,
                    'provider_response' => $resData
                ]);
                return $resData;
            } else {
                $invoice->update([
                    'status' => 'error',
                    'provider_response' => $response->json()
                ]);
                throw new \Exception("Error proveedor: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Error enviando factura: " . $e->getMessage());
            throw $e;
        }
    }
}
