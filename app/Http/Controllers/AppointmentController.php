<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $query = Appointment::with(['patient', 'doctor', 'poli']);

        if ($request->filled('date')) {
            $dayStart = Carbon::parse($request->date)->startOfDay();
            $dayEnd = Carbon::parse($request->date)->endOfDay();
            $query->whereBetween('appointment_date', [$dayStart, $dayEnd]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('poli_id')) {
            $query->where('poli_id', $request->poli_id);
        }

        $appointments = $query->latest('appointment_date')->paginate(20)->withQueryString();
        $polis = Poli::where('is_active', true)->orderBy('name')->get();

        return view('appointments.index', compact('appointments', 'polis'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Appointment::class);

        $patients = $this->resolvePatientsForCreate();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        $polis = Poli::where('is_active', true)->orderBy('name')->get();

        $schedules = collect();
        if ($request->filled('doctor_id') && $request->filled('poli_id') && $request->filled('appointment_date')) {
            $dayMap = [
                1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 0 => 'minggu',
            ];
            $day = Carbon::parse($request->appointment_date)->dayOfWeek;
            $schedules = Schedule::where('doctor_id', $request->doctor_id)
                ->where('poli_id', $request->poli_id)
                ->where('day_of_week', $dayMap[$day] ?? '')
                ->where('is_active', true)
                ->get();
        }

        return view('appointments.create', compact('patients', 'doctors', 'polis', 'schedules'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'poli_id' => 'required|exists:polis,id',
            'schedule_id' => 'required|exists:schedules,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $schedule = Schedule::findOrFail($validated['schedule_id']);
        $validated['consultation_fee'] = $schedule->consultation_fee;

        $appointment = DB::transaction(function () use ($validated, $schedule) {
            $lockName = 'queue_' . $validated['poli_id'] . '_' . $validated['appointment_date'];
            $this->acquireLock($lockName);

            try {
                $quota = $this->remainingQuota($schedule, $validated['appointment_date']);
                if ($quota <= 0) {
                    throw ValidationException::withMessages([
                        'schedule_id' => 'Kuota harian untuk jadwal ini sudah penuh.',
                    ]);
                }

                $validated['queue_number'] = $this->generateQueueNumber($validated['poli_id'], $validated['appointment_date']);
                $validated['status'] = 'waiting';

                return Appointment::create($validated);
            } finally {
                $this->releaseLock($lockName);
            }
        });

        Cache::forget(QueueDisplayController::cacheKey(Carbon::parse($validated['appointment_date'])));
        Cache::forget('dashboard.' . Carbon::parse($validated['appointment_date'])->format('Y-m-d'));

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Appointment created. Queue number: {$appointment->queue_number}");
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'doctor', 'poli', 'schedule', 'medicalRecord']);

        return view('appointments.show', compact('appointment'));
    }

    public function queue()
    {
        $this->authorize('viewAny', Appointment::class);

        $today = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd = $today->copy()->endOfDay();

        $appointments = Appointment::with(['patient', 'doctor', 'poli'])
            ->whereBetween('appointment_date', [$dayStart, $dayEnd])
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('queue_number')
            ->get();

        $groups = $appointments->groupBy('poli_id')->sortBy(fn ($group) => $group->first()->poli?->name);

        return view('appointments.queue', compact('groups', 'today'));
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'status' => 'required|in:waiting,in_progress,completed,cancelled',
        ]);

        $allowedTransitions = [
            'waiting' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (! in_array($validated['status'], $allowedTransitions[$appointment->status] ?? [])) {
            return back()->with('error', 'Transisi status tidak valid.');
        }

        $appointment->update($validated);

        Cache::forget(QueueDisplayController::cacheKey($appointment->appointment_date));
        Cache::forget('dashboard.' . $appointment->appointment_date->format('Y-m-d'));

        if ($request->boolean('back') && $request->input('back') === 'queue') {
            return redirect()->route('appointments.queue')
                ->with('success', 'Status kunjungan diperbarui.');
        }

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Status kunjungan diperbarui.');
    }

    private function resolvePatientsForCreate()
    {
        $selectedId = old('patient_id') ?: request('patient_id');

        if ($selectedId) {
            $selected = Patient::find($selectedId);
            if ($selected) {
                return collect([$selected]);
            }
        }

        return collect();
    }

    private function generateQueueNumber($poliId, $date)
    {
        $poliCode = Poli::where('id', $poliId)->value('code');
        $formattedDate = Carbon::parse($date)->format('Ymd');

        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $lastQueue = Appointment::where('poli_id', $poliId)
            ->whereBetween('appointment_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        $sequence = str_pad($lastQueue + 1, 3, '0', STR_PAD_LEFT);

        return "Q{$poliCode}-{$formattedDate}-{$sequence}";
    }

    private function remainingQuota(Schedule $schedule, $date): int
    {
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $used = Appointment::where('schedule_id', $schedule->id)
            ->whereBetween('appointment_date', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        return max(0, $schedule->daily_quota - $used);
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
}
