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

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari pasien / poli..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">No. Antrian</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Poli</th>
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
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-info-600 hover:bg-info-700 text-white text-xs font-semibold rounded-lg shadow-glass-sm transition-all duration-200">Periksa</button>
                            </form>
                        @elseif($appointment->status === 'in_progress')
                            <form action="{{ route('appointments.status.update', $appointment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-success-600 hover:bg-success-700 text-white text-xs font-semibold rounded-lg shadow-glass-sm transition-all duration-200">Selesai</button>
                            </form>
                        @endif
                        @if($appointment->medicalRecord)
                            <a href="{{ route('medical-records.show', $appointment->medicalRecord) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Rekam Medis</a>
                        @elseif(in_array($appointment->status, ['waiting', 'in_progress']))
                            <a href="{{ route('medical-records.create', $appointment) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg shadow-glass-sm transition-all duration-200">Buat Rekam Medis</a>
                        @endif
                        <a href="{{ route('appointments.show', $appointment) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Detail</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="5" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada pasien untuk Anda hari ini.</td></tr>
            @endforelse
        </x-table>
    </div>
</div>
@endsection