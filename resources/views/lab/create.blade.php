@extends('layouts.app')
@section('title', 'Buat Permintaan Laboratorium')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Buat Permintaan Laboratorium</h2>

    <form action="{{ route('lab.requests.store') }}" method="POST" x-data="{ urgent: false, selectedCount: 0 }" @change="selectedCount = document.querySelectorAll('input[name=\'lab_test_ids[]\']:checked').length">
        @csrf

        @if($appointment)
        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
        <input type="hidden" name="patient_id" value="{{ $appointment->patient_id }}">
        @if($medicalRecord)<input type="hidden" name="medical_record_id" value="{{ $medicalRecord->id }}">@endif

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Pasien</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Nama:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $appointment->patient->name }}</strong></div>
                <div><span class="text-text-secondary-light dark:text-text-secondary-dark">No. Antrian:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $appointment->queue_number }}</strong></div>
                <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Dokter:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $appointment->doctor?->name ?? '-' }}</strong></div>
                <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Poli:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $appointment->poli?->name ?? '-' }}</strong></div>
            </div>
        </div>
        @else
        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-danger-200 dark:border-danger-800">
            <p class="text-sm text-danger-600 dark:text-danger-400">Form ini harus dibuka dari halaman pasien/appointment yang berisi rekam medis.</p>
        </div>
        @endif

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Pilih Pemeriksaan</h3>
                <label class="flex items-center gap-2 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                    <input type="checkbox" name="is_urgent" value="1" x-model="urgent"
                        class="h-4 w-4 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-surface-dark dark:ring-offset-background-dark">
                    Prioritas (urgent)
                </label>
            </div>

            @error('lab_test_ids')
                <div class="mb-4 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 px-4 py-3 text-sm text-danger-700 dark:text-danger-400">{{ $message }}</div>
            @enderror

            @forelse($categories as $category => $group)
            <div class="mb-6">
                <h4 class="mb-3 text-sm font-bold uppercase tracking-wider text-primary-700 dark:text-primary-400">{{ $category }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($group as $test)
                    <label class="flex items-center justify-between gap-3 rounded-xl border border-border-light dark:border-border-dark px-4 py-3 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
                        <span class="flex items-center gap-3">
                            <input type="checkbox" name="lab_test_ids[]" value="{{ $test->id }}"
                                class="h-4 w-4 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-surface-dark dark:ring-offset-background-dark">
                            <span class="text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $test->name }}</span>
                        </span>
                        <span class="text-sm font-mono text-text-secondary-light dark:text-text-secondary-dark">Rp {{ number_format($test->price, 0, ',', '.') }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @empty
            <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">Belum ada tes laboratorium yang aktif. Tambahkan di <a href="{{ route('lab.tests') }}" class="text-primary-600 dark:text-primary-400 font-semibold">Master Tes</a>.</p>
            @endforelse

            <div>
                <x-input-label value="Catatan (Opsional)" />
                <textarea name="notes" rows="3" placeholder="Instruksi khusus untuk petugas lab..." class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark" x-text="selectedCount + ' pemeriksaan dipilih'">0 pemeriksaan dipilih</p>
                <div class="flex gap-3">
                    <x-secondary-button href="{{ route('lab.requests') }}">Batal</x-secondary-button>
                    @if($appointment)
                    <x-primary-button type="submit">Simpan Permintaan</x-primary-button>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@endsection