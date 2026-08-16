<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class QueueDisplayController extends Controller
{
    public static function cacheKey(Carbon $date = null): string
    {
        return 'queue.display.' . ($date ?? Carbon::today())->format('Y-m-d');
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
                ->groupBy(function ($item) {
                    return [
                        'poli_id' => $item->poli->id,
                        'poli_name' => $item->poli->name,
                        'poli_code' => $item->poli->code,
                    ];
                });

            $inProgress = Appointment::with(['patient', 'poli', 'doctor'])
                ->whereBetween('appointment_date', [$dayStart, $dayEnd])
                ->where('status', 'in_progress')
                ->orderBy('queue_number')
                ->get()
                ->groupBy(function ($item) {
                    return [
                        'poli_id' => $item->poli->id,
                        'poli_name' => $item->poli->name,
                        'poli_code' => $item->poli->code,
                    ];
                });

            $result = [];

            $allPoliIds = collect(array_merge(
                $waiting->keys()->toArray(),
                $inProgress->keys()->toArray()
            ))->unique();

            foreach ($allPoliIds as $poliKey) {
                $poliData = is_array($poliKey) ? $poliKey : json_decode($poliKey, true);
                $poliId = $poliData['poli_id'];

                $result[] = [
                    'poli_id' => $poliId,
                    'poli_name' => $poliData['poli_name'],
                    'poli_code' => $poliData['poli_code'],
                    'in_progress' => $inProgress->get($poliKey, collect())->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'queue_number' => $item->queue_number,
                            'patient_name' => $item->patient->name,
                            'doctor_name' => $item->doctor->name,
                        ];
                    })->values(),
                    'waiting' => $waiting->get($poliKey, collect())->map(function ($item) {
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
}
