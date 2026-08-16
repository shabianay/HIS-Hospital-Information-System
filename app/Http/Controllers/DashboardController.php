<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $this->authorize('view-dashboard');

        $today = Carbon::today();
        $cacheKey = 'dashboard.' . $today->format('Y-m-d');

        $stats = Cache::remember($cacheKey, 60, function () use ($today) {
            $dayStart = $today->copy()->startOfDay();
            $dayEnd = $today->copy()->endOfDay();

            $totalPatientsToday = DB::table('patients')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();

            $appointmentsToday = DB::table('appointments')
                ->whereBetween('appointment_date', [$dayStart, $dayEnd])
                ->count();

            $revenueToday = DB::table('billings')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->where('status', 'paid')
                ->sum('paid_amount');

            $topDiagnoses = DB::table('medical_records')
                ->join('diagnoses', 'medical_records.id', '=', 'diagnoses.medical_record_id')
                ->select('diagnoses.icd_code', 'diagnoses.description', DB::raw('COUNT(*) as count'))
                ->whereBetween('medical_records.created_at', [$dayStart, $dayEnd])
                ->groupBy('diagnoses.icd_code', 'diagnoses.description')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $recentAppointments = DB::table('appointments')
                ->join('patients', 'appointments.patient_id', '=', 'patients.id')
                ->join('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                ->join('polis', 'appointments.poli_id', '=', 'polis.id')
                ->select(
                    'appointments.id',
                    'appointments.queue_number',
                    'appointments.status',
                    'appointments.appointment_date',
                    'patients.name as patient_name',
                    'doctors.name as doctor_name',
                    'polis.name as poli_name'
                )
                ->orderByDesc('appointments.created_at')
                ->limit(10)
                ->get();

            $lowStockCount = DB::table('medicines')
                ->leftJoin('medicine_stocks', 'medicines.id', '=', 'medicine_stocks.medicine_id')
                ->select('medicines.id')
                ->groupBy('medicines.id', 'medicines.minimum_stock')
                ->havingRaw('COALESCE(SUM(medicine_stocks.quantity), 0) <= medicines.minimum_stock')
                ->get();

            $chart = $this->buildChartData($today);

            return compact(
                'totalPatientsToday',
                'appointmentsToday',
                'revenueToday',
                'topDiagnoses',
                'recentAppointments',
                'chart'
            ) + ['lowStockCount' => $lowStockCount->count()];
        });

        return view('dashboard.index', $stats);
    }

    private function buildChartData(Carbon $today)
    {
        $start = $today->copy()->subDays(13)->startOfDay();
        $end = $today->copy()->endOfDay();

        $revenueByDay = DB::table('billings')
            ->select('created_at', 'paid_amount')
            ->whereBetween('created_at', [$start, $end])
            ->where('status', 'paid')
            ->get();

        $visitsByDay = DB::table('appointments')
            ->select('appointment_date', 'poli_id')
            ->whereBetween('appointment_date', [$start, $end])
            ->get();

        $labels = collect();
        $revenueSeries = collect();
        $visitsSeries = collect();

        for ($i = 13; $i >= 0; $i--) {
            $labels->push($today->copy()->subDays($i)->format('d/m'));
            $revenueSeries->push(0);
            $visitsSeries->push(0);
        }

        $dateIndex = [];
        for ($i = 13; $i >= 0; $i--) {
            $keyDate = $today->copy()->subDays($i)->format('Y-m-d');
            $dateIndex[$keyDate] = 13 - $i;
        }

        foreach ($revenueByDay as $row) {
            $key = Carbon::parse($row->created_at)->format('Y-m-d');
            if (array_key_exists($key, $dateIndex)) {
                $revenueSeries[$dateIndex[$key]] += (float) $row->paid_amount;
            }
        }

        foreach ($visitsByDay as $row) {
            $key = Carbon::parse($row->appointment_date)->format('Y-m-d');
            if (array_key_exists($key, $dateIndex)) {
                $visitsSeries[$dateIndex[$key]] += 1;
            }
        }

        $visitsByPoli = $visitsByDay
            ->groupBy('poli_id')
            ->map(fn ($rows) => $rows->count())
            ->sortDesc();

        $poliNames = DB::table('polis')->pluck('name', 'id');
        $poliSeries = [];
        $poliLabels = [];
        foreach ($visitsByPoli as $poliId => $count) {
            $poliLabels[] = (string) ($poliNames[$poliId] ?? 'Id ' . $poliId);
            $poliSeries[] = $count;
        }

        return [
            'labels' => $labels->values(),
            'revenueSeries' => $revenueSeries->values()->map(fn ($v) => round((float) $v, 2)),
            'visitsSeries' => $visitsSeries->values(),
            'poliLabels' => $poliLabels,
            'poliSeries' => $poliSeries,
        ];
    }
}
