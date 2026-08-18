<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentCreated;
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

        $date = $request->get('date', Carbon::today()->toDateString());
        if ($date) {
            $dayStart = Carbon::parse($date)->startOfDay();
            $dayEnd = Carbon::parse($date)->endOfDay();
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

    public function indexCsv(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $query = Appointment::with(['patient', 'doctor', 'poli']);

        $date = $request->get('date', Carbon::today()->toDateString());
        if ($date) {
            $dayStart = Carbon::parse($date)->startOfDay();
            $dayEnd = Carbon::parse($date)->endOfDay();
            $query->whereBetween('appointment_date', [$dayStart, $dayEnd]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('poli_id')) {
            $query->where('poli_id', $request->poli_id);
        }

        $appointments = $query->latest('appointment_date')->get();

        $statusLabels = [
            'waiting' => 'Menunggu',
            'in_progress' => 'Sedang Diperiksa',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $filename = 'daftar-janji-' . Carbon::parse($date)->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($appointments, $statusLabels, $date) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['DAFTAR JANJI TEMU ' . Carbon::parse($date)->format('d/m/Y')]);
            fputcsv($handle, ['No. Antrian', 'Pasien', 'NIK', 'Poli', 'Dokter', 'Status', 'Jadwal']);

            foreach ($appointments as $appointment) {
                fputcsv($handle, [
                    $appointment->queue_number,
                    $appointment->patient?->name ?? '-',
                    $appointment->patient?->nik ?? '-',
                    $appointment->poli?->name ?? '-',
                    $appointment->doctor?->name ?? '-',
                    $statusLabels[$appointment->status] ?? $appointment->status,
                    $appointment->appointment_date?->format('d/m/Y H:i') ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
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

        $todayAppointments = Appointment::whereDate('appointment_date', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->get();
        $queueSummary = [
            'total' => $todayAppointments->count(),
            'waiting' => $todayAppointments->where('status', 'waiting')->count(),
            'checked_in' => $todayAppointments->where('status', 'checked_in')->count(),
            'in_progress' => $todayAppointments->where('status', 'in_progress')->count(),
            'completed' => $todayAppointments->where('status', 'completed')->count(),
        ];

        return view('appointments.create', compact('patients', 'doctors', 'polis', 'schedules', 'queueSummary'));
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

        $this->notifyDoctorOfNewAppointment($appointment);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Appointment created. Queue number: {$appointment->queue_number}");
    }

    private function notifyDoctorOfNewAppointment(Appointment $appointment): void
    {
        $doctorUser = $appointment->doctor?->user;

        if (! $doctorUser) {
            return;
        }

        $appointment->load('patient');

        $doctorUser->notify(new AppointmentCreated($appointment));
    }

    private function notifyDoctorOfCancellation(Appointment $appointment): void
    {
        $doctorUser = $appointment->doctor?->user;

        if (! $doctorUser) {
            return;
        }

        $appointment->load('patient');

        $doctorUser->notify(new AppointmentCancelled($appointment));
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'doctor', 'poli', 'schedule', 'medicalRecord']);

        return view('appointments.show', compact('appointment'));
    }

    public function ticket(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'doctor', 'poli', 'schedule']);

        return view('appointments.ticket', compact('appointment'));
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

    public function queueCsv(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $date = $request->get('date', Carbon::today()->toDateString());
        $dayStart = Carbon::parse($date)->startOfDay();
        $dayEnd = Carbon::parse($date)->endOfDay();

        $appointments = Appointment::with(['patient', 'doctor', 'poli'])
            ->whereBetween('appointment_date', [$dayStart, $dayEnd])
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('queue_number')
            ->get();

        $statusLabels = [
            'waiting' => 'Menunggu',
            'in_progress' => 'Sedang Diperiksa',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $filename = 'antrian-' . Carbon::parse($date)->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($appointments, $date, $statusLabels) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ANTRIAN PASIEN ' . Carbon::parse($date)->format('d/m/Y')]);
            fputcsv($handle, ['No. Antrian', 'Pasien', 'NIK', 'Poli', 'Dokter', 'Status']);

            foreach ($appointments as $appointment) {
                fputcsv($handle, [
                    $appointment->queue_number,
                    $appointment->patient?->name ?? '-',
                    $appointment->patient?->nik ?? '-',
                    $appointment->poli?->name ?? '-',
                    $appointment->doctor?->name ?? '-',
                    $statusLabels[$appointment->status] ?? $appointment->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function myPatients()
    {
        $doctor = auth()->user()?->doctor;
        if (! $doctor) {
            return back()->with('error', 'Akun Anda tidak tertaut ke data dokter.');
        }

        $today = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd = $today->copy()->endOfDay();

        $appointments = Appointment::with(['patient', 'poli', 'medicalRecord', 'labRequests'])
            ->where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$dayStart, $dayEnd])
            ->whereIn('status', ['waiting', 'in_progress', 'completed'])
            ->orderBy('queue_number')
            ->get();

        $mySchedules = Schedule::with('poli')
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('appointments.my-patients', compact('appointments', 'today', 'mySchedules'));
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

        if ($validated['status'] === 'cancelled') {
            $this->notifyDoctorOfCancellation($appointment);
        }

        if ($request->boolean('back') && $request->input('back') === 'queue') {
            return redirect()->route('appointments.queue')
                ->with('success', 'Status kunjungan diperbarui.');
        }

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Status kunjungan diperbarui.');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        if ($appointment->medicalRecord()->exists() || $appointment->billing()->exists() || $appointment->labRequests()->exists()) {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'Janji temu tidak dapat dihapus karena sudah memiliki rekam medis, tagihan, atau permintaan lab.');
        }

        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', 'Janji temu berhasil dihapus.');
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
