<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);

        $query = Patient::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $patients = $query->latest()->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        $this->authorize('create', Patient::class);

        return view('patients.create');
    }

    public function search(Request $request)
    {
        $this->authorize('create', Appointment::class);

        $q = trim((string) $request->get('q', ''));

        $query = Patient::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('nik', 'like', "%{$q}%")
                    ->orWhere('phone_number', 'like', "%{$q}%");
            });
        }

        $patients = $query->orderBy('name')->limit(20)->get(['id', 'name', 'nik']);

        return response()->json($patients->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'nik' => $p->nik,
            'label' => "{$p->name} — {$p->nik}",
        ]));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:16|min:16|unique:patients,nik',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:L,P',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_number' => 'nullable|string|max:50',
        ]);

        $validated['rm_number'] = $this->generateRmNumber();

        Patient::create($validated);

        Cache::forget('dashboard.' . now()->format('Y-m-d'));

        return redirect()->route('patients.index')->with('success', 'Pasien berhasil didaftarkan.');
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);

        $recentVisits = $patient->appointments()
            ->with(['doctor', 'poli'])
            ->latest('appointment_date')
            ->limit(10)
            ->get();

        return view('patients.show', compact('patient', 'recentVisits'));
    }

    public function card(Patient $patient)
    {
        $this->authorize('view', $patient);

        $pdf = Pdf::loadView('patients.card', [
            'patient' => $patient,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper([0, 0, 210, 148], 'portrait');

        return $pdf->download('kartu-berobat-' . str_replace(['-', '/', ' '], '', $patient->rm_number) . '.pdf');
    }

    public function edit(Patient $patient)
    {
        $this->authorize('update', $patient);

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:16|min:16|unique:patients,nik,'.$patient->id,
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:L,P',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_number' => 'nullable|string|max:50',
        ]);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function destroy(Patient $patient)
    {
        $this->authorize('delete', $patient);

        if ($patient->appointments()->exists()) {
            return redirect()->route('patients.index')
                ->with('error', 'Pasien tidak dapat dihapus karena memiliki riwayat kunjungan.');
        }

        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Pasien berhasil dihapus.');
    }

    private function generateRmNumber(): string
    {
        $year = now()->format('Y');
        $nextId = (Patient::max('id') ?? 0) + 1;

        return "RM-{$year}-" . str_pad((string) $nextId, 5, '0', STR_PAD_LEFT);
    }
}
