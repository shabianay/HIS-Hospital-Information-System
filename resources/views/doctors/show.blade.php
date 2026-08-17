@extends('layouts.app')
@section('title', 'Detail Dokter')
@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
            <div>
                <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $doctor->name }}</h2>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mt-1">{{ $doctor->specialization ?: 'Spesialisasi belum diisi' }}</p>
            </div>
            <div class="flex gap-2">
                <x-secondary-button href="{{ route('doctors.edit', $doctor) }}">Edit</x-secondary-button>
                <x-secondary-button href="{{ route('doctors.index') }}">Kembali</x-secondary-button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. SIP / STR</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $doctor->license_number }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Status</span>
                @if($doctor->is_active)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                @else
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                @endif
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Akun Login</span>
                @if($doctor->user)
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $doctor->user->name }}</span>
                    <span class="block text-sm font-mono text-text-secondary-light dark:text-text-secondary-dark">{{ $doctor->user->email }}</span>
                @else
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">-</span>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="text-xl font-semibold text-text-primary-light dark:text-text-primary-dark mb-6">Jadwal Praktek</h3>
        <x-table placeholder="Cari poli / hari..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Poli</th>
                    <th class="pb-4 px-4 font-semibold">Hari</th>
                    <th class="pb-4 px-4 font-semibold">Jam</th>
                    <th class="pb-4 px-4 font-semibold">Kuota Harian</th>
                    <th class="pb-4 px-4 font-semibold">Biaya Konsultasi</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                </tr>
            </x-slot>
            @forelse($schedules as $schedule)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td class="py-3 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $schedule->poli?->name }}</td>
                        <td class="py-3 px-4 capitalize text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $schedule->day_of_week }}</td>
                        <td class="py-3 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</td>
                        <td class="py-3 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $schedule->daily_quota }}</td>
                        <td class="py-3 px-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($schedule->consultation_fee, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">
                            @if($schedule->is_active)
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="6" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada jadwal praktek.</td></tr>
                    @endforelse
            </x-table>
    </div>
</div>
@endsection