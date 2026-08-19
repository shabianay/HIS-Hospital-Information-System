@extends('layouts.app')
@section('title', 'Detail Rawat Inap')
@section('content')
<div class="w-full space-y-6" x-data="{ dischargeModal: false }">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
            <div>
                <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Detail Rawat Inap</h2>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mt-1">No. Registrasi: <span class="font-mono">{{ $admission->admission_number }}</span></p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $statusClass = [
                        'admitted' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                        'discharged' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                        'cancelled' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
                    ][$admission->status] ?? 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800';
                @endphp
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ \App\Models\Admission::STATUSES[$admission->status] ?? $admission->status }}</span>
                @if($admission->status === 'admitted')
                    <button type="button" @click="dischargeModal = true" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">Selesai Perawatan (Pulang)</button>
                @endif
                <a href="{{ route('admissions.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-border-light dark:border-border-dark text-sm font-semibold text-text-primary-light dark:text-text-primary-dark rounded-xl transition-all">Kembali</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 text-sm">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Pasien</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->patient?->name }}</span>
                <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">No. RM: {{ $admission->patient?->rm_number ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter Penanggung Jawab</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->doctor?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Kamar / Tempat Tidur</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->room?->name }} / TT {{ $admission->bed?->bed_number }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tipe Admisi</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\Admission::ADMISSION_TYPES[$admission->admission_type] ?? $admission->admission_type }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Masuk</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->admitted_at?->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Keluar</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->discharged_at?->format('d/m/Y H:i') ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dicatat oleh</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->admittedBy?->name ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Disahkan pulang oleh</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->dischargedBy?->name ?: '-' }}</span>
            </div>
        </div>

        @if($admission->diagnosis)
            <div class="mt-6 border-t border-border-light dark:border-border-dark pt-6">
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Diagnosis Awal</span>
                <p class="text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->diagnosis }}</p>
            </div>
        @endif
        @if($admission->discharge_reason)
            <div class="mt-6 border-t border-border-light dark:border-border-dark pt-6">
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Alasan Keluar</span>
                <p class="text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->discharge_reason }}</p>
            </div>
        @endif
        @if($admission->notes)
            <div class="mt-6 border-t border-border-light dark:border-border-dark pt-6">
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Catatan</span>
                <p class="text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->notes }}</p>
            </div>
        @endif
    </div>

    @if($admission->status === 'admitted')
    <div x-cloak>
    <div x-show="dischargeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.window.escape="dischargeModal = false">
        <div class="w-full max-w-lg rounded-2xl bg-surface-light dark:bg-surface-dark p-6 border border-border-light dark:border-border-dark shadow-glass-md" @click.outside="dischargeModal = false">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-4">Selesai Perawatan (Pulang)</h3>
            <form action="{{ route('admissions.discharge', $admission) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <x-input-label for="discharged_at" value="Tanggal Keluar" />
                        <x-text-input type="datetime-local" name="discharged_at" value="{{ old('discharged_at', now()->format('Y-m-d\TH:i')) }}" required />
                        <x-input-error :messages="$errors->get('discharged_at')" />
                    </div>
                    <div>
                        <x-input-label for="discharge_reason" value="Alasan Keluar" />
                        <select name="discharge_reason" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Alasan</option>
                            <option value="sembuh" {{ old('discharge_reason') == 'sembuh' ? 'selected' : '' }}>Sembuh</option>
                            <option value="dirujuk" {{ old('discharge_reason') == 'dirujuk' ? 'selected' : '' }}>Dirujuk ke fasilitas lain</option>
                            <option value="pulang_aps" {{ old('discharge_reason') == 'pulang_aps' ? 'selected' : '' }}>Pulang atas permintaan sendiri (APS)</option>
                            <option value="meninggal" {{ old('discharge_reason') == 'meninggal' ? 'selected' : '' }}>Meninggal dunia</option>
                        </select>
                        <x-input-error :messages="$errors->get('discharge_reason')" />
                    </div>
                    <div>
                        <x-input-label for="notes" value="Catatan" />
                        <textarea name="notes" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ old('notes', $admission->notes) }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" />
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="dischargeModal = false" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl transition-all duration-200">Batal</button>
                        <x-primary-button type="submit">Simpan</x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
    @endif
</div>
@endsection