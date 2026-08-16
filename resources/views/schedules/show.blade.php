@extends('layouts.app')
@section('title', 'Detail Jadwal')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Detail Jadwal</h2>
            <div class="flex gap-3">
                <x-secondary-button href="{{ route('schedules.edit', $schedule) }}">Edit</x-secondary-button>
                <x-secondary-button href="{{ route('schedules.index') }}">Kembali</x-secondary-button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $schedule->doctor?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Poli</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $schedule->poli?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Hari</span>
                <span class="block text-base font-medium capitalize text-text-primary-light dark:text-text-primary-dark">{{ $schedule->day_of_week }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Jam Praktek</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Kuota Harian</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $schedule->daily_quota }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Biaya Konsultasi</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($schedule->consultation_fee, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Status</span>
                @if($schedule->is_active)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                @else
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
