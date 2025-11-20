<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Expense::query();

        // Filtrer par motif
        if ($request->filled('search_reason')) {
            $query->where('reason', 'like', '%'.$request->search_reason.'%');
        }

        // Filtrer par date
        if ($request->filled('date_start')) {
            $query->whereDate('expense_date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('expense_date', '<=', $request->date_end);
        }

        // Pagination
        $expenses = $query->orderBy('expense_date', 'desc')->paginate(10);

        // Totaux
        $total = $query->sum('amount'); // total filtré
        $today = $query->where('expense_date', '>=', now()->startOfDay())->sum('amount');
        $thisWeek = $query->where('expense_date', '>=', now()->startOfWeek())->sum('amount');
        $thisMonth = $query->where('expense_date', '>=', now()->startOfMonth())->sum('amount');

        return view('back.expenses.index', compact('expenses', 'total', 'today', 'thisWeek', 'thisMonth'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExpenseRequest $request)
    {
        //
        $expense = Expense::create([
            'reason' => $request->reason,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
        ]);

        return back()->with('success', 'Dépense enrégistrée avec success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        //
        return view('back.expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExpenseRequest $request, Expense $expense)
    {
        //

        $expense->update($request->validated());

        return redirect()->route('expenses.index')->with('success', 'Dépense mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        //
        $expense->delete();

        return back()->with('success', 'Dépense supprimée avec succès.');
    }

    public function print(Request $request)
    {
        $query = Expense::query();

        // Filtrer par motif
        if ($request->filled('search_reason')) {
            $query->where('reason', 'like', '%'.$request->search_reason.'%');
        }

        // Filtrer par date
        if ($request->filled('date_start')) {
            $query->whereDate('expense_date', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('expense_date', '<=', $request->date_end);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        return view('back.expenses.print', compact('expenses'));
    }
}
