@extends('layouts.app')
@section('title', 'Pasien Saya Hari Ini')
@section('content')
@php
    $statuses = [
        'waiting' => [
            'label' => 'Menunggu',
            'color' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
        ],
        'in_progress' => [
            'label' => 'Sedang Diperiksa',
            'color' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400',
        ],
        'completed' => [
            'label' => 'Selesai',
            'color' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
        ],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Pasien Saya Hari Ini</h2>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                {{ $today->translatedFormat('l, d F Y') }} · {{ auth()->user()?->doctor?->name ?? '' }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $appointments->where('status', 'waiting')->count() }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Sedang Diperiksa</p>
            <p class="mt-1 text-2xl font-bold text-info-600 dark:text-info-400">{{ $appointments->where('status', 'in_progress')->count() }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Selesai</p>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $appointments->where('status', 'completed')->count() }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Pasien</p>
            <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $appointments->count() }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari pasien / poli..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">No. Antrian</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Poli</th>
                    <th class="pb-4 px-4 font-semibold">Lab</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse($appointments as $appointment)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                <td class="py-4 px-4 font-mono text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->queue_number }}</td>
                <td class="py-4 px-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                    {{ $appointment->patient?->name }}
                    @if($appointment->patient?->rm_number)
                        <span class="block text-xs font-mono text-text-secondary-light dark:text-text-secondary-dark">{{ $appointment->patient->rm_number }}</span>
                    @endif
                </td>
                <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $appointment->poli?->name }}</td>
                <td class="py-4 px-4">
                    @php
                        $hasLab = $appointment->labRequests->isNotEmpty();
                        $hasPendingLab = $hasLab && $appointment->labRequests->where('status', '!=', 'completed')->isNotEmpty();
                        $hasAbnormal = $hasLab && $appointment->labRequests->flatMap->items->contains('result_status', 'abnormal');
                    @endphp
                    @if(!$hasLab)
                        <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">-</span>
                    @elseif($hasAbnormal)
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800">Abnormal</span>
                    @elseif($hasPendingLab)
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800">Proses</span>
                    @else
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Selesai</span>
                    @endif
                </td>
                <td class="py-4 px-4">
                    @php $st = $statuses[$appointment->status] ?? $statuses['waiting']; @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['color'] }}">{{ $st['label'] }}</span>
                </td>
                <td class="py-4 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        @if($appointment->status === 'waiting')
                            <form action="{{ route('appointments.status.update', $appointment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <x-action-link type="submit" variant="info-solid">Periksa</x-action-link>
                            </form>
                        @elseif($appointment->status === 'in_progress')
                            <form action="{{ route('appointments.status.update', $appointment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <x-action-link type="submit" variant="success-solid">Selesai</x-action-link>
                            </form>
                        @endif
                        @if($appointment->medicalRecord)
                            <x-action-link href="{{ route('medical-records.show', $appointment->medicalRecord) }}">Rekam Medis</x-action-link>
                        @elseif(in_array($appointment->status, ['waiting', 'in_progress']))
                            <x-action-link href="{{ route('medical-records.create', $appointment) }}" variant="primary-solid">Buat Rekam Medis</x-action-link>
                        @endif
                        @can('create', App\Models\LabRequest::class)
                            <x-action-link href="{{ route('lab.create', ['appointment_id' => $appointment->id]) }}">Rujuk Lab</x-action-link>
                        @endcan
                        <x-action-link href="{{ route('appointments.show', $appointment) }}">Detail</x-action-link>
                    </div>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="6" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada pasien untuk Anda hari ini.</td></tr>
            @endforelse
        </x-table>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
        <h3 class="mb-6 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Jadwal Praktik Saya</h3>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Hari</th>
                    <th class="pb-4 px-4 font-semibold">Poli</th>
                    <th class="pb-4 px-4 font-semibold">Jam</th>
                    <th class="pb-4 px-4 font-semibold">Kuota</th>
                    <th class="pb-4 px-4 font-semibold">Biaya Konsultasi</th>
                </tr>
            </x-slot>
            @forelse($mySchedules as $schedule)
                <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors {{ strtolower(\Carbon\Carbon::now()->translatedFormat('l')) === $schedule->day_of_week ? 'bg-primary-50/60 dark:bg-primary-900/10' : '' }}">
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark capitalize">
                        {{ $schedule->day_of_week }}
                        @if(strtolower(\Carbon\Carbon::now()->translatedFormat('l')) === $schedule->day_of_week)
                            <span class="ml-1 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 px-2 py-0.5 text-[10px] uppercase">Hari ini</span>
                        @endif
                    </td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $schedule->poli?->name ?? '-' }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $schedule->daily_quota }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($schedule->consultation_fee, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr x-show="!search" data-search-row><td colspan="5" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada jadwal praktik.</td></tr>
            @endforelse
        </x-table>
    </div>
</div>
@endsection