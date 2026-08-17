<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\Tariff;
use App\Models\User;
use App\Notifications\BillingCreated;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Billing::class);

        $query = Billing::with(['appointment.patient', 'appointment.doctor', 'appointment.poli']);

        if ($request->filled('date')) {
            $dayStart = Carbon::parse($request->date)->startOfDay();
            $dayEnd = Carbon::parse($request->date)->endOfDay();
            $query->whereBetween('created_at', [$dayStart, $dayEnd]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $billings = $query->latest()->paginate(20)->withQueryString();

        return view('billings.index', compact('billings'));
    }

    public function create(Appointment $appointment)
    {
        $this->authorize('create', Billing::class);

        $appointment->load(['patient', 'doctor', 'poli', 'medicalRecord.prescriptions.medicine', 'labRequests.items']);

        if ($appointment->billing) {
            return redirect()->route('billings.show', $appointment->billing)
                ->with('info', 'Tagihan untuk kunjungan ini sudah ada.');
        }

        $totalPrescription = 0;
        if ($appointment->medicalRecord) {
            foreach ($appointment->medicalRecord->prescriptions as $prescription) {
                $totalPrescription += ($prescription->medicine->sell_price ?? 0) * $prescription->quantity;
            }
        }

        $labItems = collect();
        foreach ($appointment->labRequests as $labRequest) {
            foreach ($labRequest->items as $item) {
                $labItems->push([
                    'test_name' => $item->test_name,
                    'price' => (float) $item->price,
                ]);
            }
        }
        $totalLab = $labItems->sum('price');

        $consultationFee = $appointment->consultation_fee ?? 0;
        $totalAmount = $consultationFee + $totalPrescription + $totalLab;

        $tariffs = Tariff::where('is_active', true)
            ->where(function ($q) use ($appointment) {
                $q->whereNull('poli_id')->orWhere('poli_id', $appointment->poli_id);
            })
            ->orderBy('name')
            ->get();

        return view('billings.create', compact(
            'appointment',
            'totalPrescription',
            'totalLab',
            'labItems',
            'consultationFee',
            'totalAmount',
            'tariffs'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Billing::class);

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'consultation_fee' => 'nullable|numeric|min:0',
            'medicine_fee' => 'nullable|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'action_fee' => 'nullable|numeric|min:0',
            'tariff_ids' => 'nullable|array',
            'tariff_ids.*' => 'exists:tariffs,id',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment = Appointment::with('medicalRecord.prescriptions.medicine')
            ->findOrFail($validated['appointment_id']);

        if ($appointment->billing) {
            return redirect()->route('billings.show', $appointment->billing)
                ->with('info', 'Tagihan untuk kunjungan ini sudah ada.');
        }

        $tariffs = Tariff::whereIn('id', $validated['tariff_ids'] ?? [])->get();
        $tariffTotal = $tariffs->sum('price');

        $consultationFee = (float) ($validated['consultation_fee'] ?? 0);
        $medicineFee = (float) ($validated['medicine_fee'] ?? 0);
        $labFee = (float) ($validated['lab_fee'] ?? 0);
        $actionFee = (float) ($validated['action_fee'] ?? 0);
        $discount = (float) ($validated['discount'] ?? 0);
        $totalAmount = max(0, $consultationFee + $medicineFee + $labFee + $tariffTotal + $actionFee - $discount);

        $billing = DB::transaction(function () use ($appointment, $totalAmount, $discount, $validated) {
            $this->acquireLock('billing_invoice');

            try {
                return Billing::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'total_amount' => $totalAmount,
                    'discount' => $discount,
                    'status' => 'unpaid',
                    'notes' => $validated['notes'] ?? null,
                ]);
            } finally {
                $this->releaseLock('billing_invoice');
            }
        });

        $items = [];
        if ($consultationFee > 0) {
            $items[] = [
                'billing_id' => $billing->id,
                'description' => 'Biaya Konsultasi Dokter',
                'type' => 'konsultasi',
                'quantity' => 1,
                'unit_price' => $consultationFee,
                'subtotal' => $consultationFee,
            ];
        }
        if ($medicineFee > 0) {
            $items[] = [
                'billing_id' => $billing->id,
                'description' => 'Biaya Obat-obatan & Alkes',
                'type' => 'obat',
                'quantity' => 1,
                'unit_price' => $medicineFee,
                'subtotal' => $medicineFee,
            ];
        }
        if ($labFee > 0) {
            $items[] = [
                'billing_id' => $billing->id,
                'description' => 'Biaya Laboratorium',
                'type' => 'lab',
                'quantity' => 1,
                'unit_price' => $labFee,
                'subtotal' => $labFee,
            ];
        }
        foreach ($tariffs as $tariff) {
            $items[] = [
                'billing_id' => $billing->id,
                'description' => $tariff->name,
                'type' => $tariff->type,
                'quantity' => 1,
                'unit_price' => $tariff->price,
                'subtotal' => $tariff->price,
            ];
        }
        if ($actionFee > 0) {
            $items[] = [
                'billing_id' => $billing->id,
                'description' => 'Biaya Tindakan / Penunjang',
                'type' => 'tindakan',
                'quantity' => 1,
                'unit_price' => $actionFee,
                'subtotal' => $actionFee,
            ];
        }

        if (! empty($items)) {
            BillingItem::insert($items);
        }

        $this->forgetDashboardCache(Carbon::parse($billing->created_at));

        $cashiers = User::role('cashier')->get();
        foreach ($cashiers as $cashier) {
            if ($cashier->id !== auth()->id()) {
                $cashier->notify(new BillingCreated($billing));
            }
        }

        return redirect()->route('billings.show', $billing)
            ->with('success', 'Tagihan berhasil dibuat.');
    }

    public function show(Billing $billing)
    {
        $this->authorize('view', $billing);

        $billing->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'appointment.medicalRecord.prescriptions.medicine',
            'billingItems',
        ]);

        return view('billings.show', compact('billing'));
    }

    public function update(Request $request, Billing $billing)
    {
        $this->authorize('update', $billing);

        $validated = $request->validate([
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,qris,bpjs,insurance',
            'status' => 'required|in:unpaid,partial,paid,cancelled',
        ]);

        if ($validated['status'] === 'paid' && $validated['paid_amount'] < $billing->total_amount) {
            return back()->withErrors(['paid_amount' => 'Nominal bayar harus sama dengan total untuk status lunas.'])->withInput();
        }

        $data = [
            'paid_amount' => $validated['paid_amount'],
            'payment_method' => $validated['payment_method'],
            'status' => $validated['status'],
            'paid_at' => in_array($validated['status'], ['paid', 'partial']) ? now() : null,
        ];

        $billing->update($data);

        return redirect()->route('billings.show', $billing)
            ->with('success', 'Tagihan berhasil diperbarui.');
    }

    public function processPayment(Request $request, Billing $billing)
    {
        $this->authorize('update', $billing);

        $validated = $request->validate([
            'payment_method' => 'required|in:cash,card,qris,bpjs,insurance',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        if ($validated['paid_amount'] < $billing->total_amount) {
            $billing->update([
                'paid_amount' => $validated['paid_amount'],
                'payment_method' => $validated['payment_method'],
                'status' => 'partial',
                'paid_at' => now(),
            ]);

            return redirect()->route('billings.show', $billing)
                ->with('success', 'Pembayaran sebagian berhasil dicatat.');
        }

        $billing->update([
            'paid_amount' => $billing->total_amount,
            'payment_method' => $validated['payment_method'],
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->forgetDashboardCache(Carbon::parse($billing->created_at));

        return redirect()->route('billings.show', $billing)
            ->with('success', 'Pembayaran berhasil, tagihan lunas.');
    }

    public function receipt(Billing $billing)
    {
        $this->authorize('view', $billing);

        $billing->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'appointment.medicalRecord.prescriptions.medicine',
            'billingItems',
        ]);

        return view('billings.receipt', compact('billing'));
    }

    public function dailyReport(Request $request)
    {
        $this->authorize('viewAny', Billing::class);

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $billings = Billing::with(['appointment.patient', 'appointment.doctor'])
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get();

        $totalRevenue = $billings->where('status', 'paid')->sum('paid_amount');
        $totalPending = $billings->where('status', 'unpaid')->sum('total_amount');
        $totalPartial = $billings->where('status', 'partial')->sum('paid_amount');
        $totalTransactions = $billings->count();
        $paidTransactions = $billings->where('status', 'paid')->count();

        $paymentMethodBreakdown = $billings->where('status', 'paid')
            ->groupBy('payment_method')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('paid_amount'),
                ];
            });

        return view('billings.daily-report', compact(
            'date',
            'billings',
            'totalRevenue',
            'totalPending',
            'totalPartial',
            'totalTransactions',
            'paidTransactions',
            'paymentMethodBreakdown'
        ));
    }

    public function dailyReportPdf(Request $request)
    {
        $this->authorize('viewAny', Billing::class);

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $billings = Billing::with(['appointment.patient', 'appointment.doctor'])
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get();

        $totalRevenue = $billings->where('status', 'paid')->sum('paid_amount');
        $totalPending = $billings->where('status', 'unpaid')->sum('total_amount');
        $totalPartial = $billings->where('status', 'partial')->sum('paid_amount');
        $totalTransactions = $billings->count();
        $paidTransactions = $billings->where('status', 'paid')->count();

        $paymentMethodBreakdown = $billings->where('status', 'paid')
            ->groupBy('payment_method')
            ->map(fn ($items) => [
                'count' => $items->count(),
                'total' => $items->sum('paid_amount'),
            ]);

        $pdf = Pdf::loadView('billings.daily-report-pdf', compact(
            'date',
            'billings',
            'totalRevenue',
            'totalPending',
            'totalPartial',
            'totalTransactions',
            'paidTransactions',
            'paymentMethodBreakdown'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-billing-' . Carbon::parse($date)->format('Ymd') . '.pdf');
    }

    public function dailyReportCsv(Request $request)
    {
        $this->authorize('viewAny', Billing::class);

        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $billings = Billing::with(['appointment.patient', 'appointment.doctor'])
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get();

        $totalRevenue = $billings->where('status', 'paid')->sum('paid_amount');
        $totalPending = $billings->where('status', 'unpaid')->sum('total_amount');
        $totalPartial = $billings->where('status', 'partial')->sum('paid_amount');

        $paymentMethodLabels = [
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'qris' => 'QRIS',
            'bpjs' => 'BPJS',
            'insurance' => 'Asuransi',
        ];

        $filename = 'laporan-billing-' . Carbon::parse($date)->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($billings, $date, $totalRevenue, $totalPending, $totalPartial, $paymentMethodLabels) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['LAPORAN BILLING HARIAN']);
            fputcsv($handle, ['Tanggal', Carbon::parse($date)->format('d/m/Y')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Total Pendapatan', number_format($totalRevenue, 2)]);
            fputcsv($handle, ['Total Belum Dibayar', number_format($totalPending, 2)]);
            fputcsv($handle, ['Total Pembayaran Sebagian', number_format($totalPartial, 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['No. Invoice', 'Pasien', 'Dokter', 'Metode', 'Total', 'Dibayar', 'Status']);
            foreach ($billings as $billing) {
                fputcsv($handle, [
                    $billing->invoice_number,
                    $billing->appointment?->patient?->name ?? '-',
                    $billing->appointment?->doctor?->name ?? '-',
                    $paymentMethodLabels[$billing->payment_method] ?? $billing->payment_method,
                    number_format((float) $billing->total_amount, 2),
                    number_format((float) $billing->paid_amount, 2),
                    $billing->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function generateInvoiceNumber()
    {
        $date = Carbon::now()->format('Ymd');
        $dayStart = Carbon::today()->startOfDay();
        $dayEnd = Carbon::today()->endOfDay();
        $lastCount = Billing::whereBetween('created_at', [$dayStart, $dayEnd])->count();

        return 'INV-'.$date.'-'.str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);
    }

    private function acquireLock(string $name): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::selectOne('SELECT GET_LOCK(?, 10)', [$name]);
    }

    private function releaseLock(string $name): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::selectOne('SELECT RELEASE_LOCK(?)', [$name]);
    }

    private function forgetDashboardCache(Carbon $date): void
    {
        Cache::forget('dashboard.' . $date->format('Y-m-d'));
    }
}
