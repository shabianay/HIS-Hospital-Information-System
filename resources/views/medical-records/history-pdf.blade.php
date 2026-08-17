<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Medis Pasien</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 16px; font-size: 10px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .info .label { color: #64748b; font-size: 9px; text-transform: uppercase; }
        .info .value { font-weight: bold; font-size: 11px; }
        h2.visit { font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 2px solid #cbd5e1; padding-bottom: 3px; margin: 18px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table th, table td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
        table th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
        .body { margin: 0 0 6px; padding: 6px 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
        .footer { margin-top: 24px; font-size: 10px; color: #94a3b8; text-align: center; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <h1>Riwayat Medis Pasien</h1>
    <div class="sub">Dicetak: {{ $generatedAt }} · Jumlah kunjungan: {{ $records->count() }}</div>

    <table class="info">
        <tr>
            <td><span class="label">Nama</span><span class="value">{{ $patient->name }}</span></td>
            <td><span class="label">No. RM</span><span class="value">{{ $patient->rm_number ?: '-' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">NIK</span><span class="value">{{ $patient->nik ?: '-' }}</span></td>
            <td><span class="label">Jenis Kelamin</span><span class="value">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Tanggal Lahir</span><span class="value">{{ $patient->date_of_birth?->format('d/m/Y') ?? '-' }}</span></td>
            <td><span class="label">Telepon</span><span class="value">{{ $patient->phone_number ?: '-' }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Alamat</span><span class="value">{{ $patient->address ?: '-' }}</span></td>
        </tr>
    </table>

    @forelse($records as $index => $record)
    @if($index > 0 && $index % 3 === 0)
    <div class="page-break"></div>
    @endif
    <h2 class="visit">Kunjungan {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }} — {{ $record->appointment?->appointment_date?->format('d/m/Y') ?? '-' }}</h2>
    <table>
        <tr>
            <td><span class="label">Dokter</span><span class="value">{{ $record->appointment?->doctor?->name ?? '-' }}</span></td>
            <td><span class="label">Poli</span><span class="value">{{ $record->appointment?->poli?->name ?? '-' }}</span></td>
            <td><span class="label">Status</span><span class="value">{{ $record->status === 'finalized' ? 'Final' : 'Draft' }}</span></td>
        </tr>
    </table>

    @if($record->chief_complaint)
    <p class="body"><strong>Keluhan Utama:</strong> {{ $record->chief_complaint }}</p>
    @endif

    <p class="body"><strong>S (Subjective):</strong> {{ $record->subjective ?: '-' }}</p>
    <p class="body"><strong>O (Objective):</strong> {{ $record->objective ?: '-' }}</p>
    <p class="body"><strong>A (Assessment):</strong> {{ $record->assessment ?: '-' }}</p>
    <p class="body"><strong>P (Plan):</strong> {{ $record->plan ?: '-' }}</p>

    <table>
        <thead><tr><th>Diagnosis (ICD-10)</th></tr></thead>
        <tbody>
            @forelse($record->diagnoses as $diagnosis)
            <tr><td>{{ $diagnosis->icd_code }} — {{ $diagnosis->description }}{{ $diagnosis->is_primary ? ' (Primer)' : '' }}</td></tr>
            @empty
            <tr><td>Tidak ada diagnosis.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($record->prescriptions->isNotEmpty())
    <table>
        <thead><tr><th>Resep Obat</th></tr></thead>
        <tbody>
            @foreach($record->prescriptions as $prescription)
            <tr><td>{{ $prescription->medicine?->name ?? '-' }} — {{ $prescription->quantity }} {{ $prescription->medicine?->unit ?? '' }} · {{ $prescription->dosage }} · {{ $prescription->frequency }}{{ $prescription->duration ? ' · ' . $prescription->duration : '' }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @empty
    <p class="body">Pasien belum memiliki rekam medis.</p>
    @endforelse

    <div class="footer">Dokumen ini dihasilkan oleh sistem HIS dan merupakan ringkasan riwayat medis pasien.</div>
</body>
</html>
