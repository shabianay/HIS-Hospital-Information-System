<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Berobat</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; background: #ffffff; }
        .card {
            width: 210mm;
            height: 148mm;
            border: 3px solid #1d4ed8;
            border-radius: 12px;
            box-sizing: border-box;
            padding: 14mm 12mm;
            color: #0f172a;
            position: relative;
            page-break-after: always;
        }
        .card:last-child { page-break-after: auto; }
        .card-head { display: flex; align-items: center; gap: 12px; border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 10px; }
        .logo { width: 44px; height: 44px; border-radius: 10px; background: #1d4ed8; color: #ffffff; font-weight: bold; font-size: 20px; display: flex; align-items: center; justify-content: center; }
        .head-text h1 { font-size: 15px; margin: 0; text-transform: uppercase; letter-spacing: 1px; color: #1d4ed8; }
        .head-text p { margin: 0; font-size: 9px; color: #475569; }
        .rm { text-align: right; }
        .rm .label { font-size: 8px; text-transform: uppercase; color: #64748b; }
        .rm .value { font-family: DejaVu Sans Mono, monospace; font-size: 14px; font-weight: bold; color: #1d4ed8; letter-spacing: 1px; }
        .row { display: flex; margin: 5px 0; font-size: 11px; }
        .row .k { width: 34mm; color: #475569; }
        .row .v { font-weight: bold; }
        .photo { position: absolute; top: 24mm; right: 12mm; width: 26mm; height: 34mm; border: 1.5px solid #cbd5e1; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 8px; text-align: center; }
        .note { position: absolute; bottom: 12mm; left: 12mm; right: 12mm; font-size: 8px; color: #94a3b8; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-head">
            <div class="logo">HIS</div>
            <div class="head-text">
                <h1>Rumah Sakit HIS</h1>
                <p>Jl. Kesehatan No. 12, Jakarta · Telp (021) 1234567</p>
            </div>
            <div class="rm">
                <div class="label">No. Rekam Medis</div>
                <div class="value">{{ $patient->rm_number }}</div>
            </div>
        </div>

        <div class="photo">FOTO<br>3x4</div>

        <div style="padding-right: 32mm;">
            <div class="row"><span class="k">Nama Lengkap</span><span class="v">{{ $patient->name }}</span></div>
            <div class="row"><span class="k">NIK</span><span class="v">{{ $patient->nik }}</span></div>
            <div class="row"><span class="k">Jenis Kelamin</span><span class="v">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
            <div class="row"><span class="k">Tanggal Lahir</span><span class="v">{{ $patient->date_of_birth?->format('d/m/Y') }}</span></div>
            <div class="row"><span class="k">Alamat</span><span class="v">{{ $patient->address ?: '-' }}</span></div>
            <div class="row"><span class="k">No. Telepon</span><span class="v">{{ $patient->phone_number ?: '-' }}</span></div>
            @if($patient->insurance_provider)
                <div class="row"><span class="k">Asuransi / BPJS</span><span class="v">{{ $patient->insurance_provider }} @if($patient->insurance_number)({{ $patient->insurance_number }}) @endif</span></div>
            @endif
        </div>

        <div class="note">Kartu ini wajib dibawa setiap kali berobat. Jika hilang, hubungi loket pendaftaran. Dicetak {{ $generatedAt }}.</div>
    </div>
</body>
</html>