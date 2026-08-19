@extends('layouts.app')
@section('title', 'Terbitkan Surat Kematian')
@section('content')
<div class="w-full">
    <a href="{{ route('death-certificates.index') }}" class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:underline mb-6">← Kembali ke Daftar Surat Kematian</a>
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Terbitkan Surat Kematian</h2>

        <form method="POST" action="{{ route('death-certificates.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @csrf
            <div class="lg:col-span-2">
                <x-input-label value="Pasien *" />
                <select name="patient_id" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    <option value="">-- Pilih Pasien --</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->rm_number }} — {{ $p->name }}</option>
                    @endforeach
                </select>
                @error('patient_id') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Tanggal & Waktu Meninggal *" />
                    <x-text-input type="datetime-local" name="date_of_death" value="{{ now()->format('Y-m-d\TH:i') }}" required />
                </div>
                <div>
                    <x-input-label value="Tempat Meninggal *" />
                    <select name="place_of_death" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="Rumah Sakit">Rumah Sakit</option>
                        <option value="Rumah">Rumah</option>
                        <option value="IGD">IGD</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
            <div class="lg:col-span-2">
                <x-input-label value="Penyebab Kematian *" />
                <select name="cause_of_death" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    @foreach(\App\Models\DeathCertificate::CAUSES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-2">
                <x-input-label value="Diagnosis / Penyebab Medis" />
                <x-text-input type="text" name="diagnosis" placeholder="Contoh: Gagal jantung kongestif" />
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Nama Dokter" />
                    <x-text-input type="text" name="doctor_name" placeholder="Nama dokter yang memeriksa" />
                </div>
                <div>
                    <x-input-label value="Nama Pelapor" />
                    <x-text-input type="text" name="reporter_name" placeholder="Nama keluarga / pelapor" />
                </div>
            </div>
            <div class="lg:col-span-2">
                <x-input-label value="Hubungan dengan Pasien" />
                <x-text-input type="text" name="deceased_relation" placeholder="Contoh: Anak kandung" />
            </div>
            <div class="lg:col-span-2">
                <x-input-label value="Catatan (Opsional)" />
                <textarea name="notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
            </div>
            <div class="flex justify-end pt-4 lg:col-span-2">
                <x-primary-button type="submit">Terbitkan Surat</x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection