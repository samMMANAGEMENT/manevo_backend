<?php

namespace App\Http\Modules\Dashboard\Services;

use App\Http\Modules\Services\Model\ServiceOrder;
use App\Http\Modules\Services\Model\ServicePerformance;
use App\Http\Modules\Sales\Model\Sale;
use App\Http\Modules\Expense\Model\Expense;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getSummary($entityId, $dateStr = null)
    {
        $date = $dateStr ? Carbon::parse($dateStr)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        // 1. Service Sales
        $serviceStats = ServiceOrder::where('entity_id', $entityId)
            ->with('items')
            ->get();

        $totalServiceRevenue = $serviceStats->sum('total_net');
        $totalOperatorCommissions = 0;

        foreach ($serviceStats as $order) {
            foreach ($order->items as $item) {
                // Commission is calculated based on total_net of the item
                $totalOperatorCommissions += ($item->total_net * ($item->commission_percentage_snapshot / 100));
            }
        }

        $inventoryStats = DB::table('sales')
            ->where('entity_id', $entityId)
            ->select(
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('SUM(total_profit) as total_profit')
            )
            ->first();

        $inventoryRevenue = $inventoryStats->total_revenue ?? 0;
        $inventoryProfit = $inventoryStats->total_profit ?? 0;

        // 4. Expenses
        $totalExpenses = Expense::where('entity_id', $entityId)->sum('amount');

        // 5. Totals
        $totalRevenue = $totalServiceRevenue + $inventoryRevenue;
        $netProfit = ($totalServiceRevenue - $totalOperatorCommissions) + $inventoryProfit - $totalExpenses;

        // 6. Activity (Last 7 days)
        $last7Days = [];
        $last7DaysExpenses = [];
        for ($i = 6; $i >= 0; $i--) {
            $loopDate = Carbon::now()->subDays($i)->format('Y-m-d');

            $serviceDay = ServiceOrder::where('entity_id', $entityId)
                ->whereDate('date', $loopDate)
                ->sum('total_net');

            $inventoryDay = DB::table('sales')
                ->where('entity_id', $entityId)
                ->whereDate('date', $loopDate)
                ->sum('total');

            $expenseDay = Expense::where('entity_id', $entityId)
                ->whereDate('date', $loopDate)
                ->sum('amount');

            $last7Days[] = [
                'date' => $loopDate,
                'services' => (float) $serviceDay,
                'inventory' => (float) $inventoryDay,
                'total' => (float) ($serviceDay + $inventoryDay)
            ];

            $last7DaysExpenses[] = [
                'date' => $loopDate,
                'amount' => (float) $expenseDay
            ];
        }

        // 7. Top Operators
        $topOperators = ServicePerformance::join('service_orders', 'service_performances.order_id', '=', 'service_orders.id')
            ->join('operators', 'service_performances.operator_id', '=', 'operators.id')
            ->join('users', 'operators.user_id', '=', 'users.id')
            ->where('service_orders.entity_id', $entityId)
            ->select(
                'users.name',
                DB::raw('SUM(service_performances.total_net) as total_generated'),
                DB::raw('SUM(service_performances.total_net * (service_performances.commission_percentage_snapshot / 100)) as commission')
            )
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_generated', 'desc')
            ->limit(5)
            ->get();

        // 8. Daily Detailed Report
        $dailyReport = $this->getDailyReport($entityId, $date);

        // 9. Payout Metrics and Quick Pay Data
        $totalOperatorPaid = (float) \App\Http\Modules\OperatorPayment\Model\OperatorPayment::where('entity_id', $entityId)
            ->sum('amount');

        $totalOperatorPending = 0.0;
        $pendingPerformances = ServicePerformance::whereHas('order', function ($q) use ($entityId) {
            $q->where('entity_id', $entityId);
        })->where('is_paid_to_employee', false)->get();

        foreach ($pendingPerformances as $perf) {
            $totalOperatorPending += ($perf->total_net * ($perf->commission_percentage_snapshot / 100));
        }

        // Deduct general advances (unlinked payments) from total pending balance
        $totalUnlinkedPayments = (float) \App\Http\Modules\OperatorPayment\Model\OperatorPayment::where('entity_id', $entityId)
            ->doesntHave('performances')
            ->sum('amount');
        
        $totalOperatorPending = max(0.0, $totalOperatorPending - $totalUnlinkedPayments);

        // Group pending commissions by operator
        $pendingByOperator = ServicePerformance::join('service_orders', 'service_performances.order_id', '=', 'service_orders.id')
            ->join('operators', 'service_performances.operator_id', '=', 'operators.id')
            ->join('users', 'operators.user_id', '=', 'users.id')
            ->where('service_orders.entity_id', $entityId)
            ->where('service_performances.is_paid_to_employee', false)
            ->select(
                'operators.id as operator_id',
                'users.name as operator_name',
                DB::raw('COUNT(service_performances.id) as services_count'),
                DB::raw('SUM(service_performances.total_net * (service_performances.commission_percentage_snapshot / 100)) as pending_amount'),
                DB::raw('string_agg(service_performances.id::text, \',\') as performance_ids')
            )
            ->groupBy('operators.id', 'users.name')
            ->get()->map(function ($item) use ($entityId) {
                // Deduct operator's general advances from their pending commissions
                $opUnlinkedPaid = (float) \App\Http\Modules\OperatorPayment\Model\OperatorPayment::where('entity_id', $entityId)
                    ->where('operator_id', $item->operator_id)
                    ->doesntHave('performances')
                    ->sum('amount');

                $item->pending_amount = max(0.0, (float) $item->pending_amount - $opUnlinkedPaid);
                $item->services_count = (int) $item->services_count;
                return $item;
            })
            ->filter(function ($item) {
                return $item->pending_amount > 0;
            })->values();

        return [
            'metrics' => [
                'total_revenue' => (float) $totalRevenue,
                'service_revenue' => (float) $totalServiceRevenue,
                'inventory_revenue' => (float) $inventoryRevenue,
                'operator_commissions' => (float) $totalOperatorCommissions,
                'total_expenses' => (float) $totalExpenses,
                'net_profit' => (float) $netProfit,
                'operator_paid' => $totalOperatorPaid,
                'operator_pending' => $totalOperatorPending,
            ],
            'chart_data' => $last7Days,
            'expense_chart_data' => $last7DaysExpenses,
            'top_operators' => $topOperators,
            'daily_report' => $dailyReport,
            'pending_by_operator' => $pendingByOperator
        ];
    }

    public function getDailyReport($entityId, $dateStr)
    {
        $date = Carbon::parse($dateStr)->format('Y-m-d');
        
        $todayServices = ServiceOrder::where('entity_id', $entityId)
            ->whereDate('date', $date)
            ->with(['items.service', 'items.employee.user'])
            ->get();

        $todaySales = Sale::where('entity_id', $entityId)
            ->whereDate('date', $date)
            ->get();

        $todayExpenses = Expense::where('entity_id', $entityId)
            ->whereDate('date', $date)
            ->sum('amount');

        $dailyIncome = (float)($todayServices->sum('total_net') + $todaySales->sum('total'));
        $dailySalesProfit = (float)$todaySales->sum('total_profit');
        
        $operatorPerformances = $todayServices->flatMap->items
            ->groupBy('operator_id')
            ->map(function ($items) {
                $firstItem = $items->first();
                $name = $firstItem->employee->user->name ?? 'Empleado';
                $gross = $items->sum('total_net');
                $commission = $items->sum(fn($i) => (float)$i->total_net * ((float)$i->commission_percentage_snapshot / 100));
                
                return [
                    'name' => $name,
                    'initials' => collect(explode(' ', $name))->map(fn($n) => mb_substr($n, 0, 1))->join(''),
                    'services_count' => $items->count(),
                    'profit' => (float) ($gross - $commission),
                    'gross' => (float) $gross,
                    'services' => $items->map(fn($i) => [
                        'name' => $i->service->name ?? 'Servicio',
                        'gross' => (float) $i->total_net,
                        'commission_percentage' => (float) $i->commission_percentage_snapshot
                    ])
                ];
            })->values();

        $todayProfit = (float)($operatorPerformances->sum('profit') + $dailySalesProfit) - $todayExpenses;

        return [
            'total_income' => (float) $dailyIncome,
            'net_profit' => (float) $todayProfit,
            'profit_percentage' => $dailyIncome > 0 ? round(($todayProfit / $dailyIncome) * 100, 1) : 0,
            'product_sales' => (float) $todaySales->sum('total'),
            'services_count' => $operatorPerformances->sum('services_count'),
            'expenses' => (float) $todayExpenses,
            'payment_methods' => [
                'cash' => (float) ($todayServices->sum('cash_amount') + $todaySales->sum('cash_amount')),
                'transfer' => (float) ($todayServices->sum('transfer_amount') + $todaySales->sum('transfer_amount')),
            ],
            'operators' => $operatorPerformances
        ];
    }
}
