<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekam Medis</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 16px; font-size: 11px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .info .label { color: #64748b; font-size: 9px; text-transform: uppercase; }
        .info .value { font-weight: bold; font-size: 11px; }
        h2.section { font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin: 16px 0 6px; }
        p.body { margin: 0 0 8px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table th, table td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
        table th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
        .vitals td { width: 16%; text-align: center; font-weight: bold; }
        .vitals .k { display: block; color: #64748b; font-size: 9px; text-transform: uppercase; font-weight: normal; }
        .footer { margin-top: 24px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Rekam Medis (EMR)</h1>
    <div class="sub">Dicetak: {{ $generatedAt }} · Status: {{ $medicalRecord->status === 'finalized' ? 'Final' : 'Draft' }}</div>

    <table class="info">
        <tr>
            <td><span class="label">Pasien</span><span class="value">{{ $medicalRecord->appointment?->patient?->name ?? '-' }}</span></td>
            <td><span class="label">No. Antrian</span><span class="value">{{ $medicalRecord->appointment?->queue_number ?? '-' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Tanggal Kunjungan</span><span class="value">{{ $medicalRecord->appointment?->appointment_date?->format('d/m/Y') ?? '-' }}</span></td>
            <td><span class="label">Dokter</span><span class="value">{{ $medicalRecord->appointment?->doctor?->name ?? '-' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Poli</span><span class="value">{{ $medicalRecord->appointment?->poli?->name ?? '-' }}</span></td>
            <td><span class="label">Keluhan Utama</span><span class="value">{{ $medicalRecord->chief_complaint ?: '-' }}</span></td>
        </tr>
    </table>

    <table class="vitals">
        <tr>
            <td><span class="k">TDS</span>{{ $medicalRecord->blood_pressure_systolic ?? '-' }}</td>
            <td><span class="k">TDD</span>{{ $medicalRecord->blood_pressure_diastolic ?? '-' }}</td>
            <td><span class="k">HR</span>{{ $medicalRecord->heart_rate ?? '-' }}</td>
            <td><span class="k">Suhu</span>{{ $medicalRecord->temperature ?? '-' }} °C</td>
            <td><span class="k">BB</span>{{ $medicalRecord->weight ?? '-' }} kg</td>
            <td><span class="k">TB</span>{{ $medicalRecord->height ?? '-' }} cm</td>
        </tr>
    </table>

    @if($medicalRecord->allergy_notes)
    <p class="body" style="background:#fffbeb;border-color:#fde68a;color:#92400e"><strong>Alergi:</strong> {{ $medicalRecord->allergy_notes }}</p>
    @endif

    <h2 class="section">Subjective (S)</h2>
    <p class="body">{{ $medicalRecord->subjective ?: '-' }}</p>

    <h2 class="section">Objective (O)</h2>
    <p class="body">{{ $medicalRecord->objective ?: '-' }}</p>

    <h2 class="section">Assessment (A)</h2>
    <p class="body">{{ $medicalRecord->assessment ?: '-' }}</p>

    <h2 class="section">Plan (P)</h2>
    <p class="body">{{ $medicalRecord->plan ?: '-' }}</p>

    <h2 class="section">Diagnosis (ICD-10)</h2>
    <table>
        <thead>
            <tr><th>Kode</th><th>Deskripsi</th><th>Primer</th></tr>
        </thead>
        <tbody>
            @forelse($medicalRecord->diagnoses as $diagnosis)
            <tr>
                <td>{{ $diagnosis->icd_code }}</td>
                <td>{{ $diagnosis->description }}</td>
                <td>{{ $diagnosis->is_primary ? 'Ya' : 'Tidak' }}</td>
            </tr>
            @empty
            <tr><td colspan="3">Tidak ada diagnosis.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 class="section">Resep Obat</h2>
    <table>
        <thead>
            <tr><th>Obat</th><th>Jumlah</th><th>Dosis</th><th>Frekuensi</th><th>Durasi</th><th>Instruksi</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($medicalRecord->prescriptions as $prescription)
            <tr>
                <td>{{ $prescription->medicine?->name ?? '-' }}</td>
                <td>{{ $prescription->quantity }} {{ $prescription->medicine?->unit ?? '' }}</td>
                <td>{{ $prescription->dosage }}</td>
                <td>{{ $prescription->frequency }}</td>
                <td>{{ $prescription->duration ?: '-' }}</td>
                <td>{{ $prescription->instructions ?: '-' }}</td>
                <td>{{ $prescription->is_dispensed ? 'Didispersi' : 'Belum' }}</td>
            </tr>
            @empty
            <tr><td colspan="7">Tidak ada resep.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($medicalRecord->appointment?->labRequests?->isNotEmpty())
    <h2 class="section">Hasil Laboratorium</h2>
    @foreach($medicalRecord->appointment->labRequests as $labRequest)
    <p class="body" style="background:#f1f5f9"><strong>Permintaan Lab #{{ str_pad($labRequest->id, 3, '0', STR_PAD_LEFT) }}</strong>
        · Status: {{ $labRequest->status === 'completed' ? 'Selesai' : ($labRequest->status === 'in_progress' ? 'Diproses' : 'Menunggu') }}
        @if($labRequest->is_urgent) · <strong>URGENT</strong>@endif</p>
    @if($labRequest->notes)
    <p class="body"><strong>Catatan:</strong> {{ $labRequest->notes }}</p>
    @endif
    <table>
        <thead>
            <tr><th>Pemeriksaan</th><th>Nilai Rujukan</th><th>Hasil</th><th>Status</th><th>Catatan</th></tr>
        </thead>
        <tbody>
            @forelse($labRequest->items as $item)
            <tr>
                <td>{{ $item->test_name }}{{ $item->unit ? ' (' . $item->unit . ')' : '' }}</td>
                <td>{{ $item->reference_range ?? '-' }}</td>
                <td>{{ $item->result_value ?: '-' }}</td>
                <td>{{ $item->result_status === 'normal' ? 'Normal' : ($item->result_status === 'abnormal' ? 'Abnormal' : '-') }}</td>
                <td>{{ $item->result_notes ?: '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="5">Belum ada item pemeriksaan.</td></tr>
            @endforelse
        </tbody>
    </table>
    @endforeach
    @endif

    <div class="footer">Dokumen ini dihasilkan oleh sistem HIS dan merupakan bagian dari rekam medis pasien.</div>
</body>
</html>