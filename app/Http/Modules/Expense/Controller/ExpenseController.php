<?php

namespace App\Http\Modules\Expense\Controller;

use App\Http\Controllers\Controller;
use App\Http\Modules\Expense\Services\ExpenseService;
use App\Http\Modules\Expense\Model\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index()
    {
        return response()->json($this->expenseService->getAll());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'payment_method' => 'string',
        ]);

        return response()->json($this->expenseService->create($validated), 201);
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'category' => 'string',
            'amount' => 'numeric|min:0',
            'date' => 'date',
            'description' => 'nullable|string',
            'payment_method' => 'string',
        ]);

        return response()->json($this->expenseService->update($expense, $validated));
    }

    public function destroy(Expense $expense)
    {
        $this->expenseService->delete($expense);
        return response()->json(null, 204);
    }
}
