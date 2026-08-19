<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::with('createdBy')->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->paginate(15)->withQueryString();

        $summary = [
            'total' => (float) $query->get()->sum('amount'),
            'month_total' => (float) Expense::whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month)
                ->sum('amount'),
            'count' => Expense::count(),
        ];

        return view('expenses.index', compact('expenses', 'summary'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::with('createdBy')->latest('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->get();

        $categoryLabels = Expense::CATEGORIES;
        $methodLabels = Expense::PAYMENT_METHODS;

        $filename = 'pengeluaran-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($expenses, $categoryLabels, $methodLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA PENGELUARAN (EXPENSE)']);
            fputcsv($handle, ['No. Transaksi', 'Tanggal', 'Kategori', 'Deskripsi', 'Dibayar Kepada', 'Metode', 'Jumlah']);
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_number,
                    $expense->expense_date?->format('d/m/Y'),
                    $categoryLabels[$expense->category] ?? $expense->category,
                    $expense->description,
                    $expense->paid_to ?? '-',
                    $methodLabels[$expense->payment_method] ?? $expense->payment_method,
                    number_format((float) $expense->amount, 2),
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL PENGELUARAN', '', '', '', '', '', number_format((float) $expenses->sum('amount'), 2)]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Expense::class);

        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'description' => 'required|string|max:500',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'paid_to' => 'nullable|string|max:255',
            'payment_method' => 'required|in:' . implode(',', array_keys(Expense::PAYMENT_METHODS)),
            'notes' => 'nullable|string|max:1000',
        ]);

        $expense = DB::transaction(function () use ($validated) {
            return Expense::create($validated + [
                'expense_number' => $this->generateExpenseNumber(),
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dicatat (' . $expense->expense_number . ').');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran dihapus.');
    }

    private function generateExpenseNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Expense::where('expense_number', 'like', 'EXP-' . $date . '-%')
            ->orderByDesc('expense_number')
            ->first();

        $seq = $last ? ((int) substr($last->expense_number, -4)) + 1 : 1;

        return 'EXP-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}