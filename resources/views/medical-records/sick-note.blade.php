<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Sakit</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; line-height: 1.7; }
        .header { text-align: center; margin-bottom: 28px; }
        .header h1 { font-size: 22px; margin: 0 0 2px; text-transform: uppercase; }
        .header p { margin: 0; color: #475569; font-size: 11px; }
        .title { text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; margin-bottom: 6px; }
        .subtitle { text-align: center; font-size: 12px; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td { padding: 3px 6px; }
        td.label { width: 170px; }
        .body-text { text-align: justify; margin: 16px 0; }
        .signature { margin-top: 48px; width: 100%; }
        .signature td { text-align: center; width: 50%; vertical-align: top; }
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

    <div class="title">SURAT KETERANGAN SAKIT</div>
    <div class="subtitle">Nomor: SKS/{{ str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) }}/{{ now()->format('Y') }}</div>

    <table>
        <tr><td class="label">Nama Pasien</td><td>: {{ $medicalRecord->appointment?->patient?->name ?? '-' }}</td></tr>
        <tr><td>No. Rekam Medis</td><td>: {{ $medicalRecord->appointment?->patient?->rm_number ?? '-' }}</td></tr>
        <tr><td>NIK</td><td>: {{ $medicalRecord->appointment?->patient?->nik ?? '-' }}</td></tr>
        <tr><td>Alamat</td><td>: {{ $medicalRecord->appointment?->patient?->address ?? '-' }}</td></tr>
    </table>

    <div class="body-text">
        Dengan ini menerangkan bahwa pasien tersebut di atas telah diperiksa oleh dokter di {{ $medicalRecord->appointment?->poli?->name ?? 'Poli' }} Rumah Sakit HIS pada tanggal {{ $medicalRecord->appointment?->appointment_date?->format('d F Y') ?? '-' }} dengan diagnosis:
    </div>
    <p style="padding-left: 24px; font-weight: bold;">
        @forelse($medicalRecord->diagnoses as $diagnosis)
            {{ $loop->first ? '' : ', ' }}{{ $diagnosis->icd_code }} — {{ $diagnosis->description }}
        @empty
            -
        @endforelse
    </p>
    <div class="body-text">
        Berdasarkan hasil pemeriksaan, pasien dianjurkan untuk beristirahat di rumah selama <strong>{{ $days }} ({{ $daysWord }}) hari</strong> terhitung sejak tanggal {{ $medicalRecord->appointment?->appointment_date?->format('d F Y') ?? '-' }} sampai dengan {{ $medicalRecord->appointment?->appointment_date?->addDays($days - 1)?->format('d F Y') ?? '-' }}.
    </div>
    <div class="body-text">
        Surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
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
