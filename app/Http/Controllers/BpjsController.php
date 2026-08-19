<?php

namespace App\Http\Controllers;

use App\Models\BpjsClaim;
use App\Models\Patient;
use App\Models\SepRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BpjsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', SepRecord::class);

        $sepRecords = SepRecord::with(['patient', 'createdBy'])->latest('sep_date')->paginate(10);

        $claims = BpjsClaim::with(['patient', 'sepRecord'])->latest('claim_date')->paginate(10);

        $summary = [
            'sep_count' => SepRecord::count(),
            'claim_count' => BpjsClaim::count(),
            'total_claim' => (float) BpjsClaim::sum('total_claim'),
            'approved_total' => (float) BpjsClaim::where('status', 'disetujui')->sum('approved_amount'),
            'pending_claims' => BpjsClaim::whereIn('status', ['diajukan', 'menunggu'])->count(),
        ];

        return view('bpjs.index', compact('sepRecords', 'claims', 'summary'));
    }

    public function storeSep(Request $request)
    {
        $this->authorize('create', SepRecord::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'bpjs_number' => 'required|string|max:30',
            'jenis_pelayanan' => 'required|in:' . implode(',', array_keys(SepRecord::JENIS_PELAYANAN)),
            'sep_date' => 'required|date',
            'diagnosis' => 'nullable|string|max:255',
            'poli' => 'nullable|string|max:255',
            'faskes_perujuk' => 'nullable|string|max:255',
        ]);

        $sep = DB::transaction(function () use ($validated) {
            return SepRecord::create($validated + [
                'sep_number' => $this->generateSepNumber(),
                'status' => 'aktif',
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('bpjs.index')->with('success', 'SEP berhasil dibuat (' . $sep->sep_number . ').');
    }

    public function cancelSep(SepRecord $sepRecord)
    {
        $this->authorize('update', $sepRecord);

        $sepRecord->status = 'dibatalkan';
        $sepRecord->save();

        return redirect()->route('bpjs.index')->with('success', 'SEP ' . $sepRecord->sep_number . ' dibatalkan.');
    }

    public function storeClaim(Request $request)
    {
        $this->authorize('create', BpjsClaim::class);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'sep_record_id' => 'nullable|exists:sep_records,id',
            'claim_date' => 'required|date',
            'total_claim' => 'required|numeric|min:0',
            'jenis_klaim' => 'required|in:' . implode(',', array_keys(BpjsClaim::JENIS_KLAIM)),
            'notes' => 'nullable|string|max:1000',
        ]);

        $claim = DB::transaction(function () use ($validated) {
            return BpjsClaim::create($validated + [
                'claim_number' => $this->generateClaimNumber(),
                'status' => 'diajukan',
                'created_by' => Auth::id(),
            ]);
        });

        return redirect()->route('bpjs.index')->with('success', 'Klaim BPJS berhasil diajukan (' . $claim->claim_number . ').');
    }

    public function updateClaimStatus(Request $request, BpjsClaim $bpjsClaim)
    {
        $this->authorize('update', $bpjsClaim);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(BpjsClaim::STATUSES)),
            'approved_amount' => 'nullable|numeric|min:0',
        ]);

        $bpjsClaim->status = $validated['status'];

        if ($validated['status'] === 'disetujui') {
            $bpjsClaim->approved_amount = $validated['approved_amount'] ?? $bpjsClaim->total_claim;
        }

        $bpjsClaim->save();

        return redirect()->route('bpjs.index')->with('success', 'Status klaim ' . $bpjsClaim->claim_number . ' diperbarui.');
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', SepRecord::class);

        $claims = BpjsClaim::with(['patient', 'sepRecord'])->latest('claim_date')->get();

        $statusLabels = BpjsClaim::STATUSES;

        $filename = 'klaim-bpjs-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($claims, $statusLabels) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA KLAIM BPJS']);
            fputcsv($handle, ['No. Klaim', 'Tanggal', 'Pasien', 'No. SEP', 'Jenis', 'Total Klaim', 'Disetujui', 'Status']);
            foreach ($claims as $claim) {
                fputcsv($handle, [
                    $claim->claim_number,
                    $claim->claim_date?->format('d/m/Y'),
                    $claim->patient?->name ?? '-',
                    $claim->sepRecord?->sep_number ?? '-',
                    BpjsClaim::JENIS_KLAIM[$claim->jenis_klaim] ?? $claim->jenis_klaim,
                    number_format((float) $claim->total_claim, 2),
                    $claim->approved_amount !== null ? number_format((float) $claim->approved_amount, 2) : '-',
                    $statusLabels[$claim->status] ?? $claim->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function generateSepNumber(): string
    {
        $date = now()->format('Ymd');
        $last = SepRecord::where('sep_number', 'like', 'SEP-' . $date . '-%')
            ->orderByDesc('sep_number')
            ->first();

        $seq = $last ? ((int) substr($last->sep_number, -4)) + 1 : 1;

        return 'SEP-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function generateClaimNumber(): string
    {
        $date = now()->format('Ymd');
        $last = BpjsClaim::where('claim_number', 'like', 'KLM-' . $date . '-%')
            ->orderByDesc('claim_number')
            ->first();

        $seq = $last ? ((int) substr($last->claim_number, -4)) + 1 : 1;

        return 'KLM-' . $date . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}