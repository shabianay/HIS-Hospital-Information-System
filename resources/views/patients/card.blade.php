<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Berobat</title>
    <style>
        @page { margin: 0; }
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; background: #ffffff; }
        .card {
            width: 206mm;
            height: 144mm;
            border: 3px solid #1d4ed8;
            border-radius: 12px;
            box-sizing: border-box;
            padding: 12mm;
            color: #0f172a;
            position: absolute;
            top: 2mm;
            left: 2mm;
        }
        .card-head { display: table; width: 100%; border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; }
        .card-head .logo { display: table-cell; width: 46px; height: 46px; }
        .card-head .head-text { display: table-cell; padding-left: 12px; vertical-align: middle; }
        .card-head .rm { display: table-cell; text-align: right; vertical-align: middle; }
        .logo {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: #1d4ed8;
            color: #ffffff;
            font-weight: bold;
            font-size: 20px;
            text-align: center;
            line-height: 46px;
            display: block;
        }
        .head-text h1 { font-size: 15px; margin: 0; text-transform: uppercase; letter-spacing: 1px; color: #1d4ed8; }
        .head-text p { margin: 2px 0 0; font-size: 9px; color: #475569; }
        .rm .label { font-size: 8px; text-transform: uppercase; color: #64748b; }
        .rm .value { font-family: DejaVu Sans Mono, monospace; font-size: 14px; font-weight: bold; color: #1d4ed8; letter-spacing: 1px; }

        .info-panel {
            margin-top: 6mm;
            background: #f0f6ff;
            border: 1px solid #dbe7ff;
            border-radius: 8px;
            padding: 5mm 6mm;
        }
        .info { width: 100%; border-collapse: collapse; }
        .info td { width: 50%; vertical-align: top; padding: 2mm 3mm 2mm 0; }
        .k { display: block; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        .v { display: block; font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 1px; }
        .note { position: absolute; bottom: 8mm; left: 12mm; right: 12mm; font-size: 8px; color: #94a3b8; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-head">
            <div class="logo">HIS</div>
            <div class="head-text">
                <h1>Rumah Sakit HIS</h1>
                <p>Jl. Kesehatan No. 12, Jakarta &middot; Telp (021) 1234567</p>
            </div>
            <div class="rm">
                <div class="label">No. Rekam Medis</div>
                <div class="value">{{ $patient->rm_number }}</div>
            </div>
        </div>

        <div class="info-panel">
            <div class="info-grid">
                <div class="info-item"><span class="k">Nama Lengkap</span><span class="v">{{ $patient->name }}</span></div>
                <div class="info-item"><span class="k">NIK</span><span class="v">{{ $patient->nik }}</span></div>
                <div class="info-item"><span class="k">Jenis Kelamin</span><span class="v">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                <div class="info-item"><span class="k">Tanggal Lahir</span><span class="v">{{ $patient->date_of_birth?->format('d/m/Y') }}</span></div>
                <div class="info-item"><span class="k">No. Telepon</span><span class="v">{{ $patient->phone_number ?: '-' }}</span></div>
                <div class="info-item"><span class="k">Asuransi / BPJS</span><span class="v">{{ $patient->insurance_provider ?: '-' }} @if($patient->insurance_provider && $patient->insurance_number)({{ $patient->insurance_number }}) @endif</span></div>
                <div class="info-item" style="grid-column: span 2;"><span class="k">Alamat</span><span class="v">{{ $patient->address ?: '-' }}</span></div>
            </div>
        </div>

        <div class="note">Kartu ini wajib dibawa setiap kali berobat. Jika hilang, hubungi loket pendaftaran. Dicetak {{ $generatedAt }}.</div>
    </div>
</body>
</html>