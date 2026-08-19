<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmissionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Admission::class);

        $query = Admission::with(['patient', 'doctor', 'room', 'bed'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('admitted_at', $request->date);
        }

        $admissions = $query->paginate(15)->withQueryString();

        $counts = [
            'admitted' => Admission::where('status', 'admitted')->count(),
            'discharged' => Admission::where('status', 'discharged')->count(),
            'today' => Admission::whereDate('admitted_at', today())->count(),
        ];

        return view('inpatient.admissions.index', compact('admissions', 'counts'));
    }

    public function indexCsv(Request $request)
    {
        $this->authorize('viewAny', Admission::class);

        $query = Admission::with(['patient', 'doctor', 'room', 'bed']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $admissions = $query->orderByDesc('admitted_at')->get();

        $filename = 'data-rawat-inap-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($admissions) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA RAWAT INAP RUMAH SAKIT HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['No. Registrasi', 'Pasien', 'No. RM', 'Dokter', 'Kamar', 'Tempat Tidur', 'Tipe', 'Masuk', 'Keluar', 'Diagnosis', 'Status']);
            foreach ($admissions as $admission) {
                fputcsv($handle, [
                    $admission->admission_number,
                    $admission->patient?->name,
                    $admission->patient?->rm_number,
                    $admission->doctor?->name,
                    $admission->room?->name,
                    $admission->bed?->bed_number,
                    Admission::ADMISSION_TYPES[$admission->admission_type] ?? $admission->admission_type,
                    $admission->admitted_at?->format('d/m/Y H:i'),
                    $admission->discharged_at?->format('d/m/Y H:i'),
                    $admission->diagnosis,
                    Admission::STATUSES[$admission->status] ?? $admission->status,
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL REGISTRASI', $admissions->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', Admission::class);

        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::where('is_active', true)->orderBy('name')->get();
        $rooms = Room::where('is_active', true)
            ->withCount(['beds' => fn ($q) => $q->where('is_active', true)])
            ->withCount(['beds as available_beds_count' => fn ($q) => $q->where('is_active', true)->whereDoesntHave('admissions', fn ($a) => $a->where('status', 'admitted'))])
            ->orderBy('code')
            ->get();

        $availableBedsByRoom = Room::where('is_active', true)
            ->with(['beds' => fn ($q) => $q->where('is_active', true)->whereDoesntHave('admissions', fn ($a) => $a->where('status', 'admitted'))])
            ->get()
            ->mapWithKeys(fn ($room) => [
                $room->id => $room->beds->map(fn ($bed) => ['id' => $bed->id, 'bed_number' => $bed->bed_number])->values(),
            ]);

        return view('inpatient.admissions.create', compact('patients', 'doctors', 'rooms', 'availableBedsByRoom'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Admission::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
            'admission_type' => 'required|in:elective,emergency',
            'admitted_at' => 'required|date',
            'diagnosis' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $bed = Bed::with('room')->findOrFail($validated['bed_id']);

        if ($bed->room_id != $validated['room_id']) {
            return back()->withErrors(['bed_id' => 'Tempat tidur tidak termasuk dalam kamar yang dipilih.'])->withInput();
        }

        if (! $bed->is_active) {
            return back()->withErrors(['bed_id' => 'Tempat tidur tidak aktif.'])->withInput();
        }

        if ($bed->admissions()->where('status', 'admitted')->exists()) {
            return back()->withErrors(['bed_id' => 'Tempat tidur sudah ditempati pasien lain.'])->withInput();
        }

        $patient = Patient::findOrFail($validated['patient_id']);
        if ($patient->admissions()->where('status', 'admitted')->exists()) {
            return back()->withErrors(['patient_id' => 'Pasien ini masih dalam perawatan inap.'])->withInput();
        }

        $validated['admission_number'] = $this->generateAdmissionNumber($validated['admitted_at']);
        $validated['admitted_at'] = Carbon::parse($validated['admitted_at']);
        $validated['status'] = 'admitted';
        $validated['admitted_by'] = auth()->id();

        Admission::create($validated);

        return redirect()->route('admissions.index')->with('success', 'Pasien berhasil dirawat inap.');
    }

    public function show(Admission $admission)
    {
        $this->authorize('view', $admission);

        $admission->load(['patient', 'doctor', 'room', 'bed', 'admittedBy', 'dischargedBy']);

        return view('inpatient.admissions.show', compact('admission'));
    }

    public function discharge(Request $request, Admission $admission)
    {
        $this->authorize('update', $admission);

        if ($admission->status !== 'admitted') {
            return back()->with('error', 'Pasien ini sudah tidak dalam perawatan.');
        }

        $validated = $request->validate([
            'discharged_at' => 'required|date',
            'discharge_reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($admission, $validated) {
            $admission->update([
                'status' => 'discharged',
                'discharged_at' => Carbon::parse($validated['discharged_at']),
                'discharge_reason' => $validated['discharge_reason'],
                'notes' => $validated['notes'] ?? $admission->notes,
                'discharged_by' => auth()->id(),
            ]);
        });

        return redirect()->route('admissions.show', $admission)->with('success', 'Pasien selesai perawatan (pulang).');
    }

    private function generateAdmissionNumber($date): string
    {
        $formattedDate = Carbon::parse($date)->format('Ymd');

        $count = Admission::whereDate('admitted_at', Carbon::parse($date)->toDateString())->count();
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "INAP-{$formattedDate}-{$sequence}";
    }
}