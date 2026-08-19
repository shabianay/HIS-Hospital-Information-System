@extends('layouts.print')
@section('title', 'Surat Keterangan Kematian')
@section('content')
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        color: #1f2937;
        font-size: 13px;
        line-height: 1.6;
    }
    .sheet {
        max-width: 640px;
        margin: 0 auto;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 36px;
        background: #ffffff;
    }
    .center { text-align: center; }
    .hospital {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 1px;
        margin: 0 0 4px;
    }
    .address { font-size: 11px; color: #4b5563; margin: 0; }
    .title {
        font-size: 18px;
        font-weight: bold;
        text-decoration: underline;
        margin: 20px 0 4px;
    }
    .number { font-size: 12px; margin: 0 0 18px; }
    .para { margin: 12px 0; text-align: justify; }
    table.info { width: 100%; border-collapse: collapse; margin: 12px 0; }
    table.info td { padding: 3px 8px 3px 0; vertical-align: top; }
    .label { width: 150px; }
    .value { font-weight: bold; }
    .sign { text-align: right; margin-top: 56px; }
    .sign .who { font-weight: bold; text-decoration: underline; }
    .sign .role { font-size: 11px; color: #4b5563; margin-top: 4px; }
</style>

<div class="sheet">
    <div class="center">
        <p class="hospital">HEALTHPRO SEHAT SEJAHTERA</p>
        <p class="address">Jl. Kesehatan No. 1, Jakarta &middot; Telp. (021) 555-0001</p>
    </div>

    <div class="center">
        <p class="title">SURAT KETERANGAN KEMATIAN</p>
        <p class="number">Nomor: <strong>{{ $certificate->certificate_number }}</strong></p>
    </div>

    <p class="para">
        Yang bertanda tangan di bawah ini, menerangkan bahwa:
    </p>

    <table class="info">
        <tr>
            <td class="label">Nama</td>
            <td>: <span class="value">{{ $certificate->patient?->name }}</span></td>
        </tr>
        <tr>
            <td class="label">No. Rekam Medis</td>
            <td>: {{ $certificate->patient?->rm_number }}</td>
        </tr>
        <tr>
            <td class="label">NIK</td>
            <td>: {{ $certificate->patient?->nik }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td>: {{ $certificate->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td>: {{ $certificate->patient?->address ?? '-' }}</td>
        </tr>
    </table>

    <p class="para">
        Telah meninggal dunia pada hari {{ $certificate->date_of_death?->isoFormat('dddd') }} tanggal {{ $certificate->date_of_death?->format('d F Y') }} pukul {{ $certificate->date_of_death?->format('H:i') }} WIB, di {{ $certificate->place_of_death }}.
    </p>

    <p class="para">
        Dengan penyebab kematian: <span class="value">{{ \App\Models\DeathCertificate::CAUSES[$certificate->cause_of_death] ?? $certificate->cause_of_death }}</span>
        @if($certificate->diagnosis)
            ({{ $certificate->diagnosis }})
        @endif
    </p>

    @if($certificate->deceased_relation)
    <p class="para">
        Surat keterangan ini dibuat berdasarkan keterangan dari: {{ $certificate->reporter_name }} ({{ $certificate->deceased_relation }}).
    </p>
    @endif

    <p class="para">
        Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
    </p>

    <div class="sign">
        <p style="margin: 0 0 48px;">Yang Menyatakan,</p>
        <p class="who">{{ $certificate->doctor_name ?? $certificate->createdBy?->name ?? '....................' }}</p>
        <p class="role">Dokter / Petugas</p>
    </div>
</div>
@endsection