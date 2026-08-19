<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\EmergencyVisit;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\EmergencyVisitCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EmergencyController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', EmergencyVisit::class);

        $query = EmergencyVisit::with(['patient', 'doctor'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('triage_level')) {
            $query->where('triage_level', $request->triage_level);
        }

        if ($request->filled('date')) {
            $query->whereDate('arrived_at', $request->date);
        }

        $visits = $query->paginate(15)->withQueryString();

        $summary = [
            'waiting' => EmergencyVisit::where('status', 'waiting')->count(),
            'treatment' => EmergencyVisit::whereIn('status', ['in_triage', 'treatment', 'observation'])->count(),
            'admitted' => EmergencyVisit::where('status', 'admitted')->count(),
            'red' => EmergencyVisit::pending()->where('triage_level', 'red')->count(),
        ];

        return view('emergency.index', compact('visits', 'summary'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', EmergencyVisit::class);

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $preselectedPatient = $request->filled('patient_id') ? Patient::find($request->patient_id) : null;

        return view('emergency.create', compact('patients', 'doctors', 'preselectedPatient'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', EmergencyVisit::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'triage_level' => 'required|in:red,yellow,green,black',
            'chief_complaint' => 'required|string|max:500',
            'triage_notes' => 'nullable|string|max:2000',
            'temperature' => 'nullable|numeric|between:30,45',
            'blood_pressure_systolic' => 'nullable|integer|between:40,300',
            'blood_pressure_diastolic' => 'nullable|integer|between:20,200',
            'heart_rate' => 'nullable|integer|between:20,250',
            'respiratory_rate' => 'nullable|integer|between:4,80',
            'oxygen_saturation' => 'nullable|integer|between:50,100',
            'gcs' => 'nullable|integer|between:3,15',
        ]);

        $visit = DB::transaction(function () use ($validated) {
            return EmergencyVisit::create([
                'visit_number' => $this->generateVisitNumber(),
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'] ?? null,
                'created_by' => Auth::id(),
                'triage_level' => $validated['triage_level'],
                'chief_complaint' => $validated['chief_complaint'],
                'triage_notes' => $validated['triage_notes'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'blood_pressure_systolic' => $validated['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $validated['blood_pressure_diastolic'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'respiratory_rate' => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
                'gcs' => $validated['gcs'] ?? null,
                'status' => 'waiting',
                'arrived_at' => now(),
            ]);
        });

        $recipients = collect();

        if ($visit->doctor?->user_id) {
            $recipients->push($visit->doctor->user);
        }

        foreach (User::role('nurse')->get() as $nurse) {
            if ($nurse->id !== Auth::id()) {
                $recipients->push($nurse);
            }
        }

        foreach (User::role('admin')->get() as $admin) {
            if ($admin->id !== Auth::id()) {
                $recipients->push($admin);
            }
        }

        foreach ($recipients->unique('id') as $recipient) {
            $recipient->notify(new EmergencyVisitCreated($visit));
        }

        $this->forgetDashboardCache();

        return redirect()->route('emergency.show', $visit)
            ->with('success', 'Pasien IGD berhasil didaftarkan.');
    }

    public function show(EmergencyVisit $emergencyVisit)
    {
        $this->authorize('view', $emergencyVisit);

        $emergencyVisit->load(['patient', 'doctor', 'createdBy', 'dischargedBy']);

        return view('emergency.show', compact('emergencyVisit'));
    }

    public function update(Request $request, EmergencyVisit $emergencyVisit)
    {
        $this->authorize('update', $emergencyVisit);

        $validated = $request->validate([
            'status' => 'required|in:waiting,in_triage,treatment,observation,admitted,discharged,referred,deceased',
            'doctor_id' => 'nullable|exists:doctors,id',
            'triage_level' => 'nullable|in:red,yellow,green,black',
            'referred_to' => 'nullable|string|max:255',
            'discharge_notes' => 'nullable|string|max:2000',
            'temperature' => 'nullable|numeric|between:30,45',
            'blood_pressure_systolic' => 'nullable|integer|between:40,300',
            'blood_pressure_diastolic' => 'nullable|integer|between:20,200',
            'heart_rate' => 'nullable|integer|between:20,250',
            'respiratory_rate' => 'nullable|integer|between:4,80',
            'oxygen_saturation' => 'nullable|integer|between:50,100',
            'gcs' => 'nullable|integer|between:3,15',
        ]);

        $status = $validated['status'];

        $emergencyVisit->status = $status;
        $emergencyVisit->doctor_id = $validated['doctor_id'] ?? $emergencyVisit->doctor_id;
        $emergencyVisit->triage_level = $validated['triage_level'] ?? $emergencyVisit->triage_level;
        $emergencyVisit->referred_to = $validated['referred_to'] ?? null;
        $emergencyVisit->discharge_notes = $validated['discharge_notes'] ?? null;
        $emergencyVisit->temperature = $validated['temperature'] ?? $emergencyVisit->temperature;
        $emergencyVisit->blood_pressure_systolic = $validated['blood_pressure_systolic'] ?? $emergencyVisit->blood_pressure_systolic;
        $emergencyVisit->blood_pressure_diastolic = $validated['blood_pressure_diastolic'] ?? $emergencyVisit->blood_pressure_diastolic;
        $emergencyVisit->heart_rate = $validated['heart_rate'] ?? $emergencyVisit->heart_rate;
        $emergencyVisit->respiratory_rate = $validated['respiratory_rate'] ?? $emergencyVisit->respiratory_rate;
        $emergencyVisit->oxygen_saturation = $validated['oxygen_saturation'] ?? $emergencyVisit->oxygen_saturation;
        $emergencyVisit->gcs = $validated['gcs'] ?? $emergencyVisit->gcs;

        if (in_array($status, ['discharged', 'referred', 'deceased'])) {
            $emergencyVisit->discharged_at = now();
            $emergencyVisit->discharged_by = Auth::id();
        } else {
            $emergencyVisit->discharged_at = null;
            $emergencyVisit->discharged_by = null;
        }

        $emergencyVisit->save();

        $this->forgetDashboardCache();

        return redirect()->route('emergency.show', $emergencyVisit)
            ->with('success', 'Status kunjungan IGD berhasil diperbarui.');
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', EmergencyVisit::class);

        $query = EmergencyVisit::with(['patient', 'doctor'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $visits = $query->get();

        $statusLabels = EmergencyVisit::STATUSES;
        $triageLabels = EmergencyVisit::TRIAGE_LEVELS;

        $filename = 'kunjungan-igd-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($visits, $statusLabels, $triageLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['KUNJUNGAN IGD']);
            fputcsv($handle, ['No. Kunjungan', 'Tanggal', 'Pasien', 'Keluhan', 'Triase', 'Dokter', 'Status']);
            foreach ($visits as $visit) {
                fputcsv($handle, [
                    $visit->visit_number,
                    $visit->arrived_at?->format('d/m/Y H:i'),
                    $visit->patient?->name ?? '-',
                    $visit->chief_complaint,
                    $triageLabels[$visit->triage_level] ?? $visit->triage_level,
                    $visit->doctor?->name ?? '-',
                    $statusLabels[$visit->status] ?? $visit->status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function generateVisitNumber(): string
    {
        $date = now()->format('Ymd');
        $last = EmergencyVisit::where('visit_number', 'like', 'IGD-' . $date . '-%')
            ->orderByDesc('visit_number')
            ->first();

        $seq = $last ? ((int) substr($last->visit_number, -4)) + 1 : 1;

        return 'IGD-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('dashboard.' . now()->format('Y-m-d'));
    }
}