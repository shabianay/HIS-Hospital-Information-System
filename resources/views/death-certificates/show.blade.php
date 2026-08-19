@extends('layouts.print')
@section('title', 'Surat Keterangan Kematian')
@section('content')
<div class="max-w-2xl mx-auto bg-white text-gray-900 p-10 border border-gray-300 rounded-lg">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold tracking-wide">HEALTHPRO SEHAT SEJAHTERA</h1>
        <p class="text-sm mt-1">Jl. Kesehatan No. 1, Jakarta · Telp. (021) 555-0001</p>
    </div>

    <div class="text-center mb-8">
        <h2 class="text-xl font-bold underline">SURAT KETERANGAN KEMATIAN</h2>
        <p class="text-sm mt-1">Nomor: <span class="font-mono font-semibold">{{ $certificate->certificate_number }}</span></p>
    </div>

    <p class="text-sm leading-relaxed mb-4">
        Yang bertanda tangan di bawah ini, menerangkan bahwa:
    </p>

    <table class="text-sm w-full mb-6">
        <tr>
            <td class="py-1 pr-4 w-40">Nama</td>
            <td class="py-1">: <span class="font-semibold">{{ $certificate->patient?->name }}</span></td>
        </tr>
        <tr>
            <td class="py-1 pr-4">No. Rekam Medis</td>
            <td class="py-1">: {{ $certificate->patient?->rm_number }}</td>
        </tr>
        <tr>
            <td class="py-1 pr-4">NIK</td>
            <td class="py-1">: {{ $certificate->patient?->nik }}</td>
        </tr>
        <tr>
            <td class="py-1 pr-4">Jenis Kelamin</td>
            <td class="py-1">: {{ $certificate->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td class="py-1 pr-4">Alamat</td>
            <td class="py-1">: {{ $certificate->patient?->address ?? '-' }}</td>
        </tr>
    </table>

    <p class="text-sm leading-relaxed mb-4">
        Telah meninggal dunia pada hari {{ $certificate->date_of_death?->isoFormat('dddd') }} tanggal {{ $certificate->date_of_death?->format('d F Y') }} pukul {{ $certificate->date_of_death?->format('H:i') }} WIB, di {{ $certificate->place_of_death }}.
    </p>

    <p class="text-sm leading-relaxed mb-4">
        Dengan penyebab kematian: <span class="font-semibold">{{ \App\Models\DeathCertificate::CAUSES[$certificate->cause_of_death] ?? $certificate->cause_of_death }}</span>
        @if($certificate->diagnosis)
            ({{ $certificate->diagnosis }})
        @endif
    </p>

    @if($certificate->deceased_relation)
    <p class="text-sm leading-relaxed mb-4">
        Surat keterangan ini dibuat berdasarkan keterangan dari: {{ $certificate->reporter_name }} ({{ $certificate->deceased_relation }}).
    </p>
    @endif

    <p class="text-sm leading-relaxed mb-8">
        Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.
    </p>

    <div class="flex justify-end text-center mt-16">
        <div>
            <p class="text-sm mb-12">Yang Menyatakan,</p>
            <p class="text-sm font-semibold underline">{{ $certificate->doctor_name ?? $certificate->createdBy?->name ?? '....................' }}</p>
            <p class="text-xs mt-1">Dokter / Petugas</p>
        </div>
    </div>
</div>
@endsection