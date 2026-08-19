<?php

namespace App\Http\Controllers;

use App\Models\Immunization;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImmunizationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Immunization::class);

        $query = Immunization::with(['patient', 'createdBy'])->latest('administered_at');

        if ($request->filled('vaccine_name')) {
            $query->where('vaccine_name', $request->vaccine_name);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('administered_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('administered_at', '<=', $request->date_to);
        }

        $immunizations = $query->paginate(15)->withQueryString();

        $summary = [
            'count' => Immunization::count(),
            'due_count' => Immunization::whereDate('next_due_date', '<=', now())->whereDate('next_due_date', '>=', now()->subDays(90))->count(),
        ];

        return view('immunizations.index', compact('immunizations', 'summary'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', Immunization::class);

        $query = Immunization::with(['patient'])->latest('administered_at');

        if ($request->filled('vaccine_name')) {
            $query->where('vaccine_name', $request->vaccine_name);
        }

        $immunizations = $query->get();

        $filename = 'imunisasi-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($immunizations) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA IMUNISASI']);
            fputcsv($handle, ['Tanggal', 'Pasien', 'Vaksin', 'Dosis', 'Batch', 'Lokasi Suntik', 'Tenaga Medis', 'Jadwal Berikutnya']);
            foreach ($immunizations as $im) {
                fputcsv($handle, [
                    $im->administered_at?->format('d/m/Y'),
                    $im->patient?->name ?? '-',
                    $im->vaccine_name,
                    $im->dose ?? '-',
                    $im->batch_number ?? '-',
                    $im->site ?? '-',
                    $im->healthcare_worker ?? '-',
                    $im->next_due_date?->format('d/m/Y') ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', Immunization::class);

        $patients = Patient::orderBy('name')->get();

        return view('immunizations.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Immunization::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'vaccine_name' => 'required|string|max:255',
            'dose' => 'nullable|string|max:50',
            'administered_at' => 'required|date',
            'next_due_date' => 'nullable|date|after_or_equal:administered_at',
            'batch_number' => 'nullable|string|max:100',
            'site' => 'nullable|string|max:100',
            'healthcare_worker' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $immunization = DB::transaction(function () use ($validated) {
            return Immunization::create($validated + ['created_by' => Auth::id()]);
        });

        return redirect()->route('immunizations.index')->with('success', 'Imunisasi berhasil dicatat.');
    }

    public function destroy(Immunization $immunization)
    {
        $this->authorize('delete', $immunization);

        $immunization->delete();

        return redirect()->route('immunizations.index')->with('success', 'Catatan imunisasi dihapus.');
    }
}