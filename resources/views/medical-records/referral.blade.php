<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Rujukan</title>
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
        .body-text { text-align: justify; margin: 16px 0; }
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

    <div class="title">SURAT RUJUKAN</div>
    <div class="subtitle">Nomor: SR/{{ str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}</div>

    <table>
        <tr><td class="label">Nama Pasien</td><td>: {{ $medicalRecord->appointment?->patient?->name ?? '-' }}</td></tr>
        <tr><td>No. Rekam Medis</td><td>: {{ $medicalRecord->appointment?->patient?->rm_number ?? '-' }}</td></tr>
        <tr><td>NIK</td><td>: {{ $medicalRecord->appointment?->patient?->nik ?? '-' }}</td></tr>
        <tr><td>Jenis Kelamin</td><td>: {{ $medicalRecord->appointment?->patient?->gender ?? '-' }}</td></tr>
        <tr><td>Tanggal Lahir</td><td>: {{ $medicalRecord->appointment?->patient?->date_of_birth?->format('d F Y') ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td>: {{ $medicalRecord->appointment?->patient?->address ?? '-' }}</td></tr>
        <tr><td>Ditujukan Kepada</td><td>: <strong>{{ $destination }}</strong></td></tr>
    </table>

    <div class="body-text">
        Dengan hormat, bersama ini kami rujuk pasien di atas untuk mendapatkan pemeriksaan dan penanganan lebih lanjut di {{ $destination }}. Pasien telah diperiksa di {{ $medicalRecord->appointment?->poli?->name ?? 'Poli' }} Rumah Sakit HIS pada tanggal {{ $medicalRecord->appointment?->appointment_date?->format('d F Y') ?? '-' }}.
    </div>

    @if($medicalRecord->diagnoses->isNotEmpty())
        <table>
            <thead>
                <tr><th style="width: 80px;">Kode ICD</th><th>Diagnosis</th></tr>
            </thead>
            <tbody>
                @foreach($medicalRecord->diagnoses as $diagnosis)
                    <tr>
                        <td>{{ $diagnosis->icd_code }}</td>
                        <td>{{ $diagnosis->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($medicalRecord->appointment?->labRequests?->isNotEmpty())
        <div style="margin-top: 8px;">
            <strong>Hasil Pemeriksaan Laboratorium:</strong>
            <ul style="margin: 4px 0 0; padding-left: 20px;">
                @foreach($medicalRecord->appointment->labRequests as $labRequest)
                    @foreach($labRequest->items as $item)
                        <li>
                            {{ $item->test_name ?: $item->labTest?->name }}:
                            {{ $item->result_value ?: '-' }}
                            @if($item->result_status && $item->result_status !== 'pending')
                                ({{ strtoupper($item->result_status) }})
                            @endif
                        </li>
                    @endforeach
                @endforeach
            </ul>
        </div>
    @endif

    @if($medicalRecord->prescriptions->isNotEmpty())
        <table style="margin-top: 12px;">
            <thead>
                <tr><th>Terapi Obat</th><th>Dosis</th><th>Frekuensi</th></tr>
            </thead>
            <tbody>
                @foreach($medicalRecord->prescriptions as $prescription)
                    <tr>
                        <td>{{ $prescription->medicine?->name ?? '-' }}</td>
                        <td>{{ $prescription->dosage }}</td>
                        <td>{{ $prescription->frequency }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="body-text">
        Demikian surat rujukan ini kami buat agar dipergunakan sebagaimana mestinya. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
    </div>

    <table class="signature">
        <tr>
            <td class="place">Jakarta, {{ $generatedAt }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>
                Dokter Pemeriksa,<br><br><br><br><br>
                <strong>{{ $medicalRecord->appointment?->doctor?->name ?? '-' }}</strong><br>
                <span style="font-size: 11px;">SIP. {{ $medicalRecord->appointment?->doctor?->license_number ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <div class="footer">Dokumen ini dihasilkan oleh sistem HIS dan ditandatangani secara elektronik oleh staf medis yang berwenang.</div>
</body>
</html>