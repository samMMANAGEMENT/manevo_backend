<?php

namespace App\Http\Modules\Entity\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Entity\Model\BillingConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Get billing configuration for the authenticated entity
     */
    public function obtenerConfiguracion()
    {
        $config = BillingConfiguration::first();

        if (!$config) {
            // Return empty object with some defaults if needed
            return response()->json([
                'razon_social' => '',
                'nit' => '',
                'is_test' => true
            ]);
        }

        return response()->json($config);
    }

    /**
     * Update or create billing configuration
     */
    public function guardarConfiguracion(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'document_type' => 'required|string|max:10',
            'nit' => 'required|string|max:20',
            'dv' => 'nullable|string|max:1',
            'email_billing' => 'nullable|email|max:255',
            'phone_billing' => 'nullable|string|max:20',
            'address_billing' => 'nullable|string|max:255',
            'city_billing' => 'nullable|string|max:100',
            'tax_regime' => 'nullable|string|max:100',
            'resolution_number' => 'nullable|string|max:100',
            'resolution_date' => 'nullable|date',
            'prefix' => 'nullable|string|max:10',
            'start_range' => 'nullable|integer',
            'end_range' => 'nullable|integer',
            'software_id' => 'nullable|string|max:255',
            'software_pin' => 'nullable|string|max:255',
            'api_token' => 'nullable|string',
            'api_base_url' => 'nullable|url',
            'test_set_id' => 'nullable|string|max:255',
            'is_test' => 'boolean',
        ]);

        $config = BillingConfiguration::updateOrCreate(
            [], // Empty attributes since we only have one config per entity and trait handles filtering
            $validated
        );

        return response()->json([
            'message' => 'Configuración de facturación guardada con éxito',
            'config' => $config
        ]);
    }
}
