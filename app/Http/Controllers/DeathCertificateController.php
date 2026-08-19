<?php

namespace App\Http\Controllers;

use App\Models\DeathCertificate;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeathCertificateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', DeathCertificate::class);

        $query = DeathCertificate::with(['patient', 'createdBy'])->latest('date_of_death');

        if ($request->filled('date_from')) {
            $query->whereDate('date_of_death', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_of_death', '<=', $request->date_to);
        }

        $certificates = $query->paginate(15)->withQueryString();

        return view('death-certificates.index', compact('certificates'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', DeathCertificate::class);

        $query = DeathCertificate::with(['patient'])->latest('date_of_death');

        if ($request->filled('date_from')) {
            $query->whereDate('date_of_death', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_of_death', '<=', $request->date_to);
        }

        $certificates = $query->get();

        $causeLabels = DeathCertificate::CAUSES;

        $filename = 'surat-kematian-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($certificates, $causeLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA SURAT KEMATIAN']);
            fputcsv($handle, ['No. Surat', 'Tanggal Meninggal', 'Pasien', 'Tempat', 'Penyebab', 'Dokter', 'Pelapor']);
            foreach ($certificates as $cert) {
                fputcsv($handle, [
                    $cert->certificate_number,
                    $cert->date_of_death?->format('d/m/Y H:i'),
                    $cert->patient?->name ?? '-',
                    $cert->place_of_death,
                    $causeLabels[$cert->cause_of_death] ?? $cert->cause_of_death,
                    $cert->doctor_name ?? '-',
                    $cert->reporter_name ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', DeathCertificate::class);

        $patients = Patient::orderBy('name')->get();

        return view('death-certificates.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', DeathCertificate::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'date_of_death' => 'required|date',
            'place_of_death' => 'required|string|max:255',
            'cause_of_death' => 'required|in:' . implode(',', array_keys(DeathCertificate::CAUSES)),
            'diagnosis' => 'nullable|string|max:255',
            'deceased_relation' => 'nullable|string|max:255',
            'reporter_name' => 'nullable|string|max:255',
            'doctor_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $certificate = DB::transaction(function () use ($validated) {
            return DeathCertificate::create($validated + [
                'certificate_number' => $this->generateCertificateNumber(),
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('death-certificates.index')->with('success', 'Surat kematian berhasil diterbitkan (' . $certificate->certificate_number . ').');
    }

    public function destroy(DeathCertificate $deathCertificate)
    {
        $this->authorize('delete', $deathCertificate);

        $deathCertificate->delete();

        return redirect()->route('death-certificates.index')->with('success', 'Surat kematian dihapus.');
    }

    public function show(DeathCertificate $deathCertificate)
    {
        $this->authorize('view', $deathCertificate);

        $deathCertificate->load(['patient', 'createdBy']);

        return view('death-certificates.show', ['certificate' => $deathCertificate]);
    }

    private function generateCertificateNumber(): string
    {
        $date = now()->format('Ymd');
        $last = DeathCertificate::where('certificate_number', 'like', 'SK-' . $date . '-%')
            ->orderByDesc('certificate_number')
            ->first();

        $seq = $last ? ((int) substr($last->certificate_number, -4)) + 1 : 1;

        return 'SK-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}