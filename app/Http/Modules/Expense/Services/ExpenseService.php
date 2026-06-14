<?php

namespace App\Http\Modules\Expense\Services;

use App\Http\Modules\Expense\Model\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{
    public function getAll()
    {
        return Expense::with('user:id,name')->orderByDesc('date')->get();
    }

    public function create(array $data)
    {
        return Expense::create([
            ...$data,
            'user_id' => Auth::id(),
        ]);
    }

    public function update(Expense $expense, array $data)
    {
        $expense->update($data);
        return $expense;
    }

    public function delete(Expense $expense)
    {
        return $expense->delete();
    }
    
    public function getSummary($startDate, $endDate)
    {
        return Expense::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->get();
    }
}
