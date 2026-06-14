<?php

namespace App\Http\Modules\Dashboard\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Dashboard\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getSummary(\Illuminate\Http\Request $request)
    {
        try {
            $entityId = Auth::user()->entity_id;
            $date = $request->query('date');
            $summary = $this->dashboardService->getSummary($entityId, $date);
            return response()->json($summary);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener resumen del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDailyReport(\Illuminate\Http\Request $request)
    {
        try {
            $entityId = Auth::user()->entity_id;
            $date = $request->query('date') ?? Carbon\Carbon::now()->format('Y-m-d');
            $report = $this->dashboardService->getDailyReport($entityId, $date);
            return response()->json($report);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener reporte diario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
