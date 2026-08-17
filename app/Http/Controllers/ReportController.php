<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Diagnosis;
use App\Models\LabRequest;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\StockMutation;
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

        $data = $this->buildReport($start, $end);

        return view('reports.index', $data + ['start' => $start, 'end' => $end]);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('view-dashboard');

        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : Carbon::today()->subDays(29)->startOfDay();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : Carbon::today()->endOfDay();

        $data = $this->buildReport($start, $end);

        $pdf = Pdf::loadView('reports.pdf', $data + [
            'start' => $start,
            'end' => $end,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.pdf');
    }

    private function buildReport(Carbon $start, Carbon $end): array
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
            'lowStockCount'
        );
    }
}