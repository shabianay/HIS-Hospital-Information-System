@extends('layouts.app')
@section('title', 'Catat Imunisasi')
@section('content')
<div class="w-full">
    <a href="{{ route('immunizations.index') }}" class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:underline mb-6">← Kembali ke Daftar Imunisasi</a>
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Catat Imunisasi</h2>

        <form method="POST" action="{{ route('immunizations.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
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
            <div class="lg:col-span-2">
                <x-input-label value="Vaksin *" />
                <input type="text" name="vaccine_name" list="vaccine-list" required placeholder="Pilih atau ketik nama vaksin" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" />
                <datalist id="vaccine-list">
                    @foreach(\App\Models\Immunization::VACCINES as $val => $label)
                        <option value="{{ $val }}">{{ $label }}</option>
                    @endforeach
                </datalist>
                @error('vaccine_name') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Dosis" />
                    <x-text-input type="text" name="dose" placeholder="Contoh: 1, 2, 3, atau Booster" />
                </div>
                <div>
                    <x-input-label value="Tanggal Pemberian *" />
                    <x-text-input type="date" name="administered_at" value="{{ now()->toDateString() }}" required />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Jadwal Berikutnya (Opsional)" />
                    <x-text-input type="date" name="next_due_date" />
                </div>
                <div>
                    <x-input-label value="Nomor Batch" />
                    <x-text-input type="text" name="batch_number" placeholder="Contoh: B20260801" />
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Lokasi Suntikan" />
                    <select name="site" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">-- Pilih --</option>
                        <option value="deltoid-kiri">Deltoid Kiri</option>
                        <option value="deltoid-kanan">Deltoid Kanan</option>
                        <option value="vastus-lateralis-kiri">Vastus Lateralis Kiri</option>
                        <option value="vastus-lateralis-kanan">Vastus Lateralis Kanan</option>
                        <option value="gluteal">Gluteal</option>
                    </select>
                </div>
                <div>
                    <x-input-label value="Tenaga Medis" />
                    <x-text-input type="text" name="healthcare_worker" placeholder="Nama petugas" />
                </div>
            </div>
            <div class="lg:col-span-2">
                <x-input-label value="Catatan (Opsional)" />
                <textarea name="notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
            </div>
            <div class="flex justify-end pt-4 lg:col-span-2">
                <x-primary-button type="submit">Simpan</x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection