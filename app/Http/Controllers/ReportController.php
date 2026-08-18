<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Diagnosis;
use App\Models\LabRequest;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-dashboard');

        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : Carbon::today()->endOfDay();
        $period = $this->validPeriod($request->input('period'));

        $data = $this->buildReport($start, $end, $period);

        return view('reports.index', $data + ['start' => $start, 'end' => $end, 'period' => $period]);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('view-dashboard');

        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : Carbon::today()->endOfDay();
        $period = $this->validPeriod($request->input('period'));

        $data = $this->buildReport($start, $end, $period);

        $pdf = Pdf::loadView('reports.pdf', $data + [
            'start' => $start,
            'end' => $end,
            'period' => $period,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.pdf');
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('view-dashboard');

        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : Carbon::today()->endOfDay();
        $period = $this->validPeriod($request->input('period'));

        $data = $this->buildReport($start, $end, $period);

        $filename = 'laporan-'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($data, $start, $end, $period) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['LAPORAN RUMAH SAKIT HIS']);
            fputcsv($handle, ['Periode', $start->format('d/m/Y'), 's.d.', $end->format('d/m/Y')]);
            fputcsv($handle, ['Agregasi', $this->periodLabel($period)]);
            fputcsv($handle, []);
            fputcsv($handle, ['RINGKASAN']);
            fputcsv($handle, ['Total Pendapatan (Lunas)', number_format($data['totalRevenue'], 2)]);
            fputcsv($handle, ['Pendapatan Tertunda', number_format($data['pendingRevenue'], 2)]);
            fputcsv($handle, ['Total Kunjungan', $data['totalVisits']]);
            fputcsv($handle, ['Kunjungan Selesai', $data['completedVisits']]);
            fputcsv($handle, ['Pasien Baru', $data['newPatients']]);
            fputcsv($handle, ['Total Tagihan', $data['totalBilling']]);
            fputcsv($handle, ['Tagihan Lunas', $data['paidBilling']]);
            fputcsv($handle, []);

            fputcsv($handle, ['TREN PERIODE']);
            fputcsv($handle, ['Periode', 'Pendapatan', 'Kunjungan', 'Selesai', 'Pasien Baru']);
            foreach ($data['periodRows'] as $row) {
                fputcsv($handle, [$row['label'], number_format($row['revenue'], 2), $row['visits'], $row['completed'], $row['new_patients']]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['KUNJUNGAN PER POLI']);
            fputcsv($handle, ['Poli', 'Jumlah']);
            foreach ($data['poliVisits'] as $row) {
                fputcsv($handle, [$row->name, $row->total]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['PRODUKTIVITAS DOKTER']);
            fputcsv($handle, ['Dokter', 'Kunjungan', 'Selesai', 'Pendapatan']);
            foreach ($data['doctorProductivity'] as $row) {
                fputcsv($handle, [$row->doctor_name, $row->total_visits, $row->completed, number_format((float) $row->revenue, 2)]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['DIAGNOSIS TERBANYAK']);
            fputcsv($handle, ['Kode ICD', 'Diagnosis', 'Jumlah']);
            foreach ($data['topDiagnoses'] as $row) {
                fputcsv($handle, [$row->icd_code, $row->description, $row->total]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['KONSUMSI OBAT']);
            fputcsv($handle, ['Obat', 'Total Qty', 'Nilai', 'Jumlah Resep']);
            foreach ($data['medicineConsumption'] as $row) {
                fputcsv($handle, [$row->name, $row->total_quantity, number_format((float) $row->total_value, 2), $row->total_prescriptions]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['BEBAN LABORATORIUM']);
            fputcsv($handle, ['Status', 'Jumlah']);
            foreach ($data['labWorkload'] as $status => $row) {
                fputcsv($handle, [$status, $row->total]);
            }
            fputcsv($handle, []);

            fputcsv($handle, ['NILAI STOK OBAT']);
            fputcsv($handle, ['Obat', 'Satuan', 'Harga Beli', 'Harga Jual', 'Total Qty', 'Nilai Stok']);
            foreach ($data['stockValuation'] as $row) {
                fputcsv($handle, [$row->name, $row->unit, number_format((float) $row->buy_price, 2), number_format((float) $row->sell_price, 2), $row->total_quantity, number_format((float) $row->total_value, 2)]);
            }
            fputcsv($handle, ['TOTAL NILAI STOK', number_format((float) $data['stockValuationTotal'], 2)]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReport(Carbon $start, Carbon $end, string $period = 'harian'): array
    {
        // Summary
        $totalRevenue = (float) Billing::whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')->sum('paid_amount');
        $pendingRevenue = (float) Billing::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['unpaid', 'partial'])->sum(DB::raw('total_amount - paid_amount'));
        $totalVisits = Appointment::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])->count();
        $completedVisits = Appointment::whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'completed')->count();
        $newPatients = Patient::whereBetween('created_at', [$start, $end])->count();
        $totalBilling = Billing::whereBetween('created_at', [$start, $end])->count();
        $paidBilling = Billing::whereBetween('created_at', [$start, $end])->where('status', 'paid')->count();

        // Per-poli visits
        $poliVisits = Appointment::select('polis.name', DB::raw('COUNT(*) as total'))
            ->join('polis', 'appointments.poli_id', '=', 'polis.id')
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('polis.id', 'polis.name')
            ->orderByDesc('total')
            ->get();

        // Per-doctor productivity
        $doctorProductivity = Appointment::select(
            'doctors.name as doctor_name',
            DB::raw('COUNT(appointments.id) as total_visits'),
            DB::raw('COUNT(CASE WHEN appointments.status = "completed" THEN 1 END) as completed'),
            DB::raw('SUM(CASE WHEN billings.status = "paid" THEN billings.paid_amount ELSE 0 END) as revenue')
        )
            ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
            ->leftJoin('billings', 'billings.appointment_id', '=', 'appointments.id')
            ->whereBetween('appointments.appointment_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('doctors.id', 'doctors.name')
            ->orderByDesc('total_visits')
            ->get();

        // Top diagnoses (morbidity)
        $topDiagnoses = Diagnosis::select(
            'diagnoses.icd_code',
            'diagnoses.description',
            DB::raw('COUNT(*) as total')
        )
            ->join('medical_records', 'diagnoses.medical_record_id', '=', 'medical_records.id')
            ->whereBetween('medical_records.created_at', [$start, $end])
            ->groupBy('diagnoses.icd_code', 'diagnoses.description')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Medicine consumption
        $medicineConsumption = Prescription::select(
            'medicines.name',
            DB::raw('SUM(prescriptions.quantity) as total_quantity'),
            DB::raw('SUM(prescriptions.quantity * medicines.sell_price) as total_value'),
            DB::raw('COUNT(prescriptions.id) as total_prescriptions')
        )
            ->join('medicines', 'prescriptions.medicine_id', '=', 'medicines.id')
            ->join('medical_records', 'prescriptions.medical_record_id', '=', 'medical_records.id')
            ->whereBetween('medical_records.created_at', [$start, $end])
            ->groupBy('medicines.id', 'medicines.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Lab workload
        $labWorkload = LabRequest::select('lab_requests.status', DB::raw('COUNT(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('lab_requests.status')
            ->get()
            ->keyBy('status');

        $labTotal = $labWorkload->sum('total');

        // Stock valuation (current, as of end of period)
        $stockValuation = DB::table('medicine_stocks as ms')
            ->join('medicines as m', 'ms.medicine_id', '=', 'm.id')
            ->select(
                'm.id',
                'm.name',
                'm.unit',
                'm.buy_price',
                'm.sell_price',
                'm.minimum_stock',
                DB::raw('SUM(ms.quantity) as total_quantity'),
                DB::raw('SUM(ms.quantity * m.buy_price) as total_value')
            )
            ->groupBy('m.id', 'm.name', 'm.unit', 'm.buy_price', 'm.sell_price', 'm.minimum_stock')
            ->orderByDesc('total_value')
            ->limit(15)
            ->get();

        $stockValuationTotal = DB::table('medicine_stocks as ms')
            ->join('medicines as m', 'ms.medicine_id', '=', 'm.id')
            ->select(DB::raw('COALESCE(SUM(ms.quantity * m.buy_price), 0) as total'))
            ->value('total');

        $expiringStockCount = DB::table('medicine_stocks')
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [Carbon::today()->startOfDay(), Carbon::today()->addDays(60)->endOfDay()])
            ->count();

        $lowStockCount = DB::table('medicines as m')
            ->leftJoin('medicine_stocks as ms', 'm.id', '=', 'ms.medicine_id')
            ->select('m.id', DB::raw('COALESCE(SUM(ms.quantity), 0) as total'))
            ->groupBy('m.id')
            ->havingRaw('COALESCE(SUM(ms.quantity), 0) <= m.minimum_stock')
            ->get()
            ->count();

        $periodRows = $this->buildPeriodRows($start, $end, $period);

        return compact(
            'totalRevenue',
            'pendingRevenue',
            'totalVisits',
            'completedVisits',
            'newPatients',
            'totalBilling',
            'paidBilling',
            'poliVisits',
            'doctorProductivity',
            'topDiagnoses',
            'medicineConsumption',
            'labWorkload',
            'labTotal',
            'stockValuation',
            'stockValuationTotal',
            'expiringStockCount',
            'lowStockCount',
            'periodRows'
        );
    }

    private function validPeriod(?string $period): string
    {
        return in_array($period, ['harian', 'mingguan', 'bulanan'], true) ? $period : 'harian';
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'mingguan' => 'Mingguan',
            'bulanan' => 'Bulanan',
            default => 'Harian',
        };
    }

    private function buildPeriodRows(Carbon $start, Carbon $end, string $period): array
    {
        $billingRows = DB::table('billings')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->get(['created_at', 'paid_amount']);

        $visitRows = DB::table('appointments')
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->get(['appointment_date', 'status']);

        $patientRows = DB::table('patients')
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at']);

        $key = function (Carbon $date) use ($period) {
            return match ($period) {
                'mingguan' => $date->copy()->startOfWeek()->format('Y-m-d'),
                'bulanan' => $date->format('Y-m'),
                default => $date->format('Y-m-d'),
            };
        };

        $label = function (string $key) use ($period) {
            return match ($period) {
                'mingguan' => 'Minggu '.Carbon::parse($key)->format('d/m/Y'),
                'bulanan' => Carbon::createFromFormat('Y-m', $key)->translatedFormat('F Y'),
                default => Carbon::createFromFormat('Y-m-d', $key)->format('d/m/Y'),
            };
        };

        $rows = [];

        foreach ($billingRows as $row) {
            $k = $key(Carbon::parse($row->created_at));
            $rows[$k]['revenue'] = ($rows[$k]['revenue'] ?? 0) + (float) $row->paid_amount;
            $rows[$k]['visits'] = $rows[$k]['visits'] ?? 0;
            $rows[$k]['completed'] = $rows[$k]['completed'] ?? 0;
            $rows[$k]['new_patients'] = $rows[$k]['new_patients'] ?? 0;
        }

        foreach ($visitRows as $row) {
            $k = $key(Carbon::parse($row->appointment_date));
            $rows[$k]['revenue'] = $rows[$k]['revenue'] ?? 0;
            $rows[$k]['visits'] = ($rows[$k]['visits'] ?? 0) + 1;
            $rows[$k]['completed'] = $rows[$k]['completed'] ?? 0;
            $rows[$k]['new_patients'] = $rows[$k]['new_patients'] ?? 0;

            if ($row->status === 'completed') {
                $rows[$k]['completed'] = ($rows[$k]['completed'] ?? 0) + 1;
            }
        }

        foreach ($patientRows as $row) {
            $k = $key(Carbon::parse($row->created_at));
            $rows[$k]['revenue'] = $rows[$k]['revenue'] ?? 0;
            $rows[$k]['visits'] = $rows[$k]['visits'] ?? 0;
            $rows[$k]['completed'] = $rows[$k]['completed'] ?? 0;
            $rows[$k]['new_patients'] = ($rows[$k]['new_patients'] ?? 0) + 1;
        }

        ksort($rows);

        return collect($rows)->map(function (array $data, string $k) use ($label) {
            return [
                'label' => $label($k),
                'revenue' => round((float) ($data['revenue'] ?? 0), 2),
                'visits' => (int) ($data['visits'] ?? 0),
                'completed' => (int) ($data['completed'] ?? 0),
                'new_patients' => (int) ($data['new_patients'] ?? 0),
            ];
        })->values()->all();
    }
}
