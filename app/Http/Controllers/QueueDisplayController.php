<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LabRequest;
use Carbon\Carbon;
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
}
