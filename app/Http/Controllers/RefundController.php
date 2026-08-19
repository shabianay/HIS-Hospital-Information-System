<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Refund::class);

        $query = Refund::with(['patient', 'billing', 'processedBy'])->latest('refunded_at');

        if ($request->filled('date_from')) {
            $query->whereDate('refunded_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('refunded_at', '<=', $request->date_to);
        }

        $refunds = $query->paginate(15)->withQueryString();

        $summary = [
            'total' => (float) $refunds->sum('amount'),
            'count' => Refund::count(),
        ];

        return view('refunds.index', compact('refunds', 'summary'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', Refund::class);

        $query = Refund::with(['patient', 'billing'])->latest('refunded_at');

        if ($request->filled('date_from')) {
            $query->whereDate('refunded_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('refunded_at', '<=', $request->date_to);
        }

        $refunds = $query->get();

        $reasonLabels = Refund::REASONS;

        $filename = 'refund-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($refunds, $reasonLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA REFUND / PENGEMBALIAN DANA']);
            fputcsv($handle, ['No. Refund', 'Tanggal', 'No. Invoice', 'Pasien', 'Alasan', 'Jumlah']);
            foreach ($refunds as $refund) {
                fputcsv($handle, [
                    $refund->refund_number,
                    $refund->refunded_at?->format('d/m/Y H:i'),
                    $refund->billing?->invoice_number ?? '-',
                    $refund->patient?->name ?? '-',
                    $reasonLabels[$refund->reason] ?? $refund->reason,
                    number_format((float) $refund->amount, 2),
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL REFUND', '', '', '', '', number_format((float) $refunds->sum('amount'), 2)]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Refund::class);

        $billing = null;
        if ($request->filled('billing_id')) {
            $billing = Billing::with(['patient'])->find($request->billing_id);
        }

        $billings = Billing::with(['patient'])
            ->where('status', 'paid')
            ->whereColumn('paid_amount', '>', 'total_amount')
            ->orderByDesc('paid_at')
            ->get();

        return view('refunds.create', compact('billings', 'billing'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Refund::class);

        $validated = $request->validate([
            'billing_id' => 'required|exists:billings,id',
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|in:' . implode(',', array_keys(Refund::REASONS)),
            'notes' => 'nullable|string|max:1000',
        ]);

        $refund = DB::transaction(function () use ($validated) {
            $billing = Billing::with('patient')->lockForUpdate()->findOrFail($validated['billing_id']);

            $maxRefund = (float) $billing->paid_amount - (float) $billing->total_amount;
            $amount = (float) $validated['amount'];

            if ($maxRefund <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Tagihan ini tidak memiliki kelebihan pembayaran.',
                ]);
            }

            if ($amount > $maxRefund) {
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah refund melebihi kelebihan pembayaran (maksimal Rp ' . number_format($maxRefund, 0, ',', '.') . ').',
                ]);
            }

            $refund = Refund::create([
                'refund_number' => $this->generateRefundNumber(),
                'billing_id' => $billing->id,
                'patient_id' => $billing->patient_id,
                'amount' => $amount,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'processed_by' => Auth::id(),
                'refunded_at' => now(),
            ]);

            $billing->paid_amount = max(0, (float) $billing->paid_amount - $amount);
            $billing->save();

            return $refund;
        });

        return redirect()->route('refunds.index')->with('success', 'Refund berhasil diproses (' . $refund->refund_number . ').');
    }

    private function generateRefundNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Refund::where('refund_number', 'like', 'REF-' . $date . '-%')
            ->orderByDesc('refund_number')
            ->first();

        $seq = $last ? ((int) substr($last->refund_number, -4)) + 1 : 1;

        return 'REF-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}