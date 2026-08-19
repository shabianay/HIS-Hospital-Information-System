<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Icd9Procedure;
use App\Models\Patient;
use App\Models\Surgery;
use App\Models\User;
use App\Notifications\SurgeryScheduled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SurgeryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Surgery::class);

        $query = Surgery::with(['patient', 'doctor', 'icd9Procedure'])
            ->latest('scheduled_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        $surgeries = $query->paginate(15)->withQueryString();

        $summary = [
            'scheduled' => Surgery::where('status', 'scheduled')->count(),
            'in_progress' => Surgery::where('status', 'in_progress')->count(),
            'completed' => Surgery::where('status', 'completed')->count(),
            'major' => Surgery::pending()->where('surgery_type', 'major')->count(),
        ];

        return view('surgeries.index', compact('surgeries', 'summary'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Surgery::class);

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::orderBy('name')->get();
        $procedures = Icd9Procedure::where('is_active', true)->orderBy('code')->get();
        $preselectedPatient = $request->filled('patient_id') ? Patient::find($request->patient_id) : null;

        return view('surgeries.create', compact('patients', 'doctors', 'procedures', 'preselectedPatient'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Surgery::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'icd9_procedure_id' => 'nullable|exists:icd9_procedures,id',
            'procedure_name' => 'required|string|max:255',
            'surgery_type' => 'required|in:minor,major',
            'operating_room' => 'nullable|string|max:100',
            'scheduled_at' => 'required|date|after_or_equal:now',
            'pre_notes' => 'nullable|string|max:2000',
        ]);

        $icd9 = $request->filled('icd9_procedure_id') ? Icd9Procedure::find($validated['icd9_procedure_id']) : null;

        $surgery = DB::transaction(function () use ($validated, $icd9) {
            return Surgery::create([
                'surgery_number' => $this->generateSurgeryNumber(),
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'] ?? null,
                'icd9_procedure_id' => $validated['icd9_procedure_id'] ?? null,
                'created_by' => Auth::id(),
                'procedure_name' => $icd9?->name ?? $validated['procedure_name'],
                'surgery_type' => $validated['surgery_type'],
                'operating_room' => $validated['operating_room'] ?? null,
                'status' => 'scheduled',
                'pre_notes' => $validated['pre_notes'] ?? null,
                'scheduled_at' => $validated['scheduled_at'],
            ]);
        });

        $recipients = collect();

        if ($surgery->doctor?->user_id) {
            $recipients->push($surgery->doctor->user);
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
            $recipient->notify(new SurgeryScheduled($surgery));
        }

        $this->forgetDashboardCache();

        return redirect()->route('surgeries.show', $surgery)
            ->with('success', 'Jadwal operasi berhasil dibuat.');
    }

    public function show(Surgery $surgery)
    {
        $this->authorize('view', $surgery);

        $surgery->load(['patient', 'doctor', 'icd9Procedure', 'createdBy', 'completedBy']);

        return view('surgeries.show', compact('surgery'));
    }

    public function updateStatus(Request $request, Surgery $surgery)
    {
        $this->authorize('update', $surgery);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'post_notes' => 'nullable|string|max:2000',
        ]);

        $status = $validated['status'];

        $surgery->status = $status;
        $surgery->post_notes = $validated['post_notes'] ?? $surgery->post_notes;

        if ($status === 'in_progress') {
            $surgery->started_at = $surgery->started_at ?? now();
            $surgery->finished_at = null;
        }

        if ($status === 'completed') {
            $surgery->finished_at = now();
            $surgery->completed_by = Auth::id();
        }

        if ($status === 'cancelled') {
            $surgery->finished_at = null;
        }

        $surgery->save();

        $this->forgetDashboardCache();

        return redirect()->route('surgeries.show', $surgery)
            ->with('success', 'Status operasi berhasil diperbarui.');
    }

    public function destroy(Surgery $surgery)
    {
        $this->authorize('delete', $surgery);

        $surgery->delete();

        return redirect()->route('surgeries.index')->with('success', 'Jadwal operasi dihapus.');
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', Surgery::class);

        $query = Surgery::with(['patient', 'doctor'])->latest('scheduled_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $surgeries = $query->get();

        $statusLabels = Surgery::STATUSES;
        $typeLabels = Surgery::TYPES;

        $filename = 'jadwal-operasi-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($surgeries, $statusLabels, $typeLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['JADWAL OPERASI']);
            fputcsv($handle, ['No. Operasi', 'Tanggal', 'Pasien', 'Prosedur', 'Jenis', 'Operator', 'Kamar OK', 'Status']);
            foreach ($surgeries as $surgery) {
                fputcsv($handle, [
                    $surgery->surgery_number,
                    $surgery->scheduled_at?->format('d/m/Y H:i'),
                    $surgery->patient?->name ?? '-',
                    $surgery->procedure_name,
                    $typeLabels[$surgery->surgery_type] ?? $surgery->surgery_type,
                    $surgery->doctor?->name ?? '-',
                    $surgery->operating_room ?? '-',
                    $statusLabels[$surgery->status] ?? $surgery->status,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function generateSurgeryNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Surgery::where('surgery_number', 'like', 'OK-' . $date . '-%')
            ->orderByDesc('surgery_number')
            ->first();

        $seq = $last ? ((int) substr($last->surgery_number, -4)) + 1 : 1;

        return 'OK-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('dashboard.' . now()->format('Y-m-d'));
    }
}