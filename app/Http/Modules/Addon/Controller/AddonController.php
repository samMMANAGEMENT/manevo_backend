<?php

namespace App\Http\Modules\Addon\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Addon\Services\AddonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddonController extends Controller
{
    public function __construct(private AddonService $addonService)
    {
    }

    public function obtenerAddonesDisponibles()
    {
        try {
            $addons = $this->addonService->listarAddons();
            return response()->json($addons, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerAddonesDeMiEntidad()
    {
        try {
            $entityId = Auth::user()->entity_id;
            $addons = $this->addonService->addonsDeEntidad($entityId);
            return response()->json($addons, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function obtenerConfiguracion($slug)
    {
        try {
            $entityId = Auth::user()->entity_id;
            return response()->json($this->addonService->obtenerConfiguracion($entityId, $slug), 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function actualizarConfiguracion(Request $request, $slug)
    {
        $entityId = Auth::user()->entity_id;

        if (!$this->addonService->tieneAddonActivo($entityId, $slug)) {
            return response()->json(['message' => 'Este addon no está activo para tu negocio.'], 403);
        }

        $validated = $request->validate([
            'hours_before' => 'required|integer|min:1|max:168',
        ]);

        try {
            $config = $this->addonService->actualizarConfiguracion($entityId, $slug, $validated);
            return response()->json($config, 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
