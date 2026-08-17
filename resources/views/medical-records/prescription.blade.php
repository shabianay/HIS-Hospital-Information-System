<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Resep Obat</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.7; }
        .header { text-align: center; margin-bottom: 28px; }
        .header h1 { font-size: 22px; margin: 0 0 2px; text-transform: uppercase; }
        .header p { margin: 0; color: #475569; font-size: 11px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 6px; }
        .subtitle { text-align: center; font-size: 12px; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { padding: 6px 8px; border: 1px solid #cbd5e1; text-align: left; vertical-align: top; }
        th { background-color: #f1f5f9; font-size: 11px; text-transform: uppercase; }
        td.label { border: none; width: 170px; }
        .signature { border: none; margin-top: 48px; width: 100%; }
        .signature td { border: none; text-align: center; width: 50%; vertical-align: top; }
        .signature .place { margin-bottom: 56px; }
        .footer { margin-top: 24px; font-size: 10px; color: #94a3b8; text-align: center; }
        .divider { border: none; border-top: 1px solid #cbd5e1; margin: 12px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rumah Sakit HIS</h1>
        <p>Jl. Kesehatan No. 12, Jakarta</p>
        <p>Telp: (021) 1234567 · email: info@hishospital.id</p>
    </div>
    <div class="divider"></div>

    <div class="title">RESEP OBAT</div>
    <div class="subtitle">Nomor: R/{{ str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}</div>

    <table style="border: none;">
        <tr><td class="label">Nama Pasien</td><td style="border: none;">: {{ $medicalRecord->appointment?->patient?->name ?? '-' }}</td></tr>
        <tr><td class="label">No. Rekam Medis</td><td style="border: none;">: {{ $medicalRecord->appointment?->patient?->rm_number ?? '-' }}</td></tr>
        <tr><td class="label">Tanggal Periksa</td><td style="border: none;">: {{ $medicalRecord->appointment?->appointment_date?->format('d F Y') ?? '-' }}</td></tr>
        <tr><td class="label">Poli</td><td style="border: none;">: {{ $medicalRecord->appointment?->poli?->name ?? '-' }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 36px;">No</th>
                <th>Obat</th>
                <th style="width: 70px;">Jumlah</th>
                <th style="width: 110px;">Dosis</th>
                <th style="width: 130px;">Frekuensi</th>
                <th style="width: 90px;">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicalRecord->prescriptions as $index => $prescription)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $prescription->medicine?->name ?? '-' }}</td>
                    <td>{{ $prescription->quantity }} {{ $prescription->medicine?->unit ?? '' }}</td>
                    <td>{{ $prescription->dosage }}</td>
                    <td>{{ $prescription->frequency }}</td>
                    <td>{{ $prescription->duration ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($medicalRecord->prescriptions->contains(fn ($p) => $p->instructions))
        <div style="margin-top: 8px;">
            <strong>Catatan:</strong>
            <ul style="margin: 4px 0 0; padding-left: 20px;">
                @foreach($medicalRecord->prescriptions as $prescription)
                    @if($prescription->instructions)
                        <li>{{ $prescription->medicine?->name }} — {{ $prescription->instructions }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    <table class="signature">
        <tr>
            <td class="place">Jakarta, {{ $generatedAt }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>
                Dokter Penulis Resep,<br><br><br><br><br>
                <strong>{{ $medicalRecord->appointment?->doctor?->name ?? '-' }}</strong><br>
                <span style="font-size: 11px;">SIP. {{ $medicalRecord->appointment?->doctor?->license_number ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <div class="footer">Resep ini hanya dapat dilayani di apotek dengan menyerahkan resep asli. Dokumen dihasilkan oleh sistem HIS.</div>
</body>
</html>