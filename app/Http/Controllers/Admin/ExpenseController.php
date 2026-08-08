<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpensesExport;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'recorder');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('vendor_payee', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->date_from) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->latest('expense_date')->paginate(20)->withQueryString();

        $categories = ExpenseCategory::active()->orderBy('name')->get();

        $summary = [
            'today'       => Expense::whereDate('expense_date', today())->sum('amount'),
            'this_month'  => Expense::whereMonth('expense_date', now()->month)
                                     ->whereYear('expense_date', now()->year)->sum('amount'),
            'pending'     => Expense::where('payment_status', 'pending')->sum('amount'),
            'total_count' => Expense::count(),
        ];

        return view('admin.expenses.index', compact('expenses', 'categories', 'summary'));
    }

    public function create()
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date'        => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string|max:500',
            'amount'              => 'required|numeric|min:0.01',
            'vendor_payee'        => 'nullable|string|max:200',
            'payment_method'      => 'required|in:cash,upi,bank_transfer,cheque',
            'payment_status'      => 'required|in:paid,pending',
            'reference_number'    => 'nullable|string|max:100',
            'bill'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        $validated['recorded_by'] = auth()->id();

        if ($request->hasFile('bill')) {
            $validated['bill_path'] = $request->file('bill')->store('expenses/bills', 'uploads');
        }

        Expense::create($validated);

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense)
    {
        $expense->load('category', 'recorder');
        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::active()->orderBy('name')->get();
        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_date'        => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string|max:500',
            'amount'              => 'required|numeric|min:0.01',
            'vendor_payee'        => 'nullable|string|max:200',
            'payment_method'      => 'required|in:cash,upi,bank_transfer,cheque',
            'payment_status'      => 'required|in:paid,pending',
            'reference_number'    => 'nullable|string|max:100',
            'bill'                => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'remarks'             => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('bill')) {
            if ($expense->bill_path) {
                Storage::disk('uploads')->delete($expense->bill_path);
            }
            $validated['bill_path'] = $request->file('bill')->store('expenses/bills', 'uploads');
        }

        $expense->update($validated);

        return redirect()->route('admin.expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete(); // soft delete — preserves audit trail

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense deleted (soft-deleted for audit).');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['category_id', 'payment_status', 'payment_method', 'date_from', 'date_to']);
        $filename = 'expenses_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ExpensesExport($filters), $filename);
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['category_id', 'payment_status', 'payment_method', 'date_from', 'date_to']);
        $filename = 'expenses_' . now()->format('Ymd_His') . '.csv';
        return Excel::download(new ExpensesExport($filters), $filename, \Maatwebsite\Excel\Excel::CSV);
    }

    public function exportPdf(Request $request)
    {
        $query = Expense::with('category', 'recorder');

        if ($request->date_from) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }
        if ($request->category_id) {
            $query->where('expense_category_id', $request->category_id);
        }

        $expenses  = $query->latest('expense_date')->get();
        $total     = $expenses->sum('amount');
        $pdf       = Pdf::loadView('admin.expenses.pdf', compact('expenses', 'total'))
                        ->setPaper('a4', 'landscape');

        return $pdf->download('expenses_' . now()->format('Ymd_His') . '.pdf');
    }
}
