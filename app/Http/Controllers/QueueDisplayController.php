<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LabRequest;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QueueDisplayController extends Controller
{
    public static function cacheKey(Carbon $date = null): string
    {
        return 'queue.display.' . ($date ?? Carbon::today())->format('Y-m-d');
    }

    public static function labCacheKey(Carbon $date = null): string
    {
        return 'queue.lab.display.' . ($date ?? Carbon::today())->format('Y-m-d');
    }

    public function lookupForm()
    {
        return view('queue.lookup');
    }

    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'queue_number' => 'required|string|max:50',
        ]);

        $appointment = Appointment::with(['patient', 'doctor', 'poli'])
            ->whereDate('appointment_date', Carbon::today()->toDateString())
            ->whereRaw('LOWER(queue_number) = ?', [mb_strtolower($validated['queue_number'])])
            ->first();

        if (! $appointment) {
            return back()->withInput()->with('error', 'Nomor antrian tidak ditemukan untuk hari ini.');
        }

        $statusLabels = [
            'waiting' => 'Menunggu',
            'checked_in' => 'Sudah Hadir',
            'in_progress' => 'Sedang Diperiksa',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $ahead = $appointment->status === 'waiting'
            ? Appointment::whereDate('appointment_date', Carbon::today()->toDateString())
                ->where('poli_id', $appointment->poli_id)
                ->where('status', 'waiting')
                ->where('queue_number', '<', $appointment->queue_number)
                ->count()
            : 0;

        return view('queue.lookup', [
            'result' => $appointment,
            'statusLabel' => $statusLabels[$appointment->status] ?? $appointment->status,
            'ahead' => $ahead,
        ]);
    }

    public function index()
    {
        $dayStart = Carbon::today()->startOfDay();
        $dayEnd = Carbon::today()->endOfDay();

        $queues = Appointment::with(['patient', 'doctor', 'poli'])
            ->whereBetween('appointment_date', [$dayStart, $dayEnd])
            ->whereIn('status', ['waiting', 'in_progress'])
            ->orderBy('queue_number')
            ->get()
            ->groupBy(function ($item) {
                return $item->poli->name;
            });

        $currentInProgress = Appointment::with(['patient', 'doctor', 'poli'])
            ->whereBetween('appointment_date', [$dayStart, $dayEnd])
            ->where('status', 'in_progress')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->poli->name;
            });

        return view('queue.display', compact('queues', 'currentInProgress'));
    }

    public function getCurrentQueues()
    {
        $result = Cache::remember(static::cacheKey(), 60, function () {
            $dayStart = Carbon::today()->startOfDay();
            $dayEnd = Carbon::today()->endOfDay();

            $waiting = Appointment::with(['patient', 'poli'])
                ->whereBetween('appointment_date', [$dayStart, $dayEnd])
                ->where('status', 'waiting')
                ->orderBy('queue_number')
                ->get()
                ->groupBy('poli_id');

            $inProgress = Appointment::with(['patient', 'poli', 'doctor'])
                ->whereBetween('appointment_date', [$dayStart, $dayEnd])
                ->where('status', 'in_progress')
                ->orderBy('queue_number')
                ->get()
                ->groupBy('poli_id');

            $poliIds = $waiting->keys()->merge($inProgress->keys())->unique()->values();

            $result = [];
            foreach ($poliIds as $poliId) {
                $poli = $inProgress->get($poliId, collect())->first()?->poli
                    ?? $waiting->get($poliId, collect())->first()?->poli;

                if (! $poli) {
                    continue;
                }

                $result[] = [
                    'poli_id' => (int) $poliId,
                    'poli_name' => $poli->name,
                    'poli_code' => $poli->code,
                    'in_progress' => $inProgress->get($poliId, collect())->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'queue_number' => $item->queue_number,
                            'patient_name' => $item->patient->name,
                            'doctor_name' => $item->doctor->name,
                        ];
                    })->values(),
                    'waiting' => $waiting->get($poliId, collect())->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'queue_number' => $item->queue_number,
                            'patient_name' => $item->patient->name,
                        ];
                    })->values(),
                ];
            }

            return $result;
        });

        return response()->json($result);
    }

    public function lab()
    {
        $initial = $this->buildLabQueues();

        return view('queue.lab-display', compact('initial'));
    }

    public function getLabQueues()
    {
        return response()->json($this->buildLabQueues());
    }

    private function buildLabQueues(): array
    {
        $dayStart = Carbon::today()->startOfDay();
        $dayEnd = Carbon::today()->endOfDay();

        $requests = LabRequest::with(['patient', 'items.labTest'])
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('is_urgent', 'desc')
            ->orderBy('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'patient_name' => $item->patient?->name ?? '-',
                    'is_urgent' => (bool) $item->is_urgent,
                    'status' => $item->status,
                    'tests' => $item->items->pluck('lab_test.name')->filter()->unique()->values(),
                    'created_at' => $item->created_at?->format('H:i') ?? '-',
                ];
            })
            ->values();

        $inProgress = $requests->firstWhere('status', 'in_progress');
        $waiting = $requests->where('status', '!=', 'in_progress')->values();

        return [
            'in_progress' => $inProgress,
            'waiting' => $waiting,
            'total' => $requests->count(),
        ];
    }

    public function pharmacy()
    {
        $initial = $this->buildPharmacyQueues();

        return view('queue.pharmacy-display', compact('initial'));
    }

    public function getPharmacyQueues()
    {
        return response()->json($this->buildPharmacyQueues());
    }

    private function buildPharmacyQueues(): array
    {
        $prescriptions = Prescription::with(['medicine', 'medicalRecord.patient'])
            ->where('is_dispensed', false)
            ->whereHas('medicalRecord', fn ($q) => $q->whereHas('appointment', fn ($a) => $a->where('status', '!=', 'cancelled')))
            ->latest()
            ->get();

        $grouped = $prescriptions->groupBy('medical_record_id');

        $queue = $grouped->map(function ($items, $recordId) {
            $record = $items->first()->medicalRecord;

            return [
                'medical_record_id' => (int) $recordId,
                'patient_name' => $record?->patient?->name ?? '-',
                'queue_number' => $record?->appointment?->queue_number ?? null,
                'items' => $items->map(function ($item) {
                    return [
                        'name' => $item->medicine?->name ?? '-',
                        'quantity' => $item->quantity,
                    ];
                })->values(),
                'created_at' => $items->first()?->created_at?->format('H:i') ?? '-',
            ];
        })->values();

        return [
            'queue' => $queue,
            'total_prescriptions' => $prescriptions->count(),
            'total_patients' => $grouped->count(),
        ];
    }
}
