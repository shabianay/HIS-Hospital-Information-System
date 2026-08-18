@extends('layouts.app')
@section('title', 'Daftar Rekam Medis (EMR)')
@section('content')
@php
    $statusBadge = [
        'draft' => ['label' => 'Draft', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800'],
        'finalized' => ['label' => 'Final', 'class' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800'],
    ];
@endphp
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Rekam Medis (EMR)</h3>
        <a href="{{ route('medical-records.index.csv') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Export CSV</a>
    </div>
    <x-table placeholder="Cari pasien / poli / dokter..." class="overflow-hidden">
        <x-slot name="head">
            <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                <th class="pb-3 font-semibold">Tanggal Kunjungan</th>
                <th class="pb-3 font-semibold">Pasien</th>
                <th class="pb-3 font-semibold">Poli</th>
                <th class="pb-3 font-semibold">Dokter</th>
                <th class="pb-3 font-semibold">Status</th>
                <th class="pb-3 font-semibold">Aksi</th>
            </tr>
        </x-slot>
        @forelse($records as $record)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->appointment_date?->format('d/m/Y') }}</td>
                    <td class="py-3 font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->patient?->name }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->poli?->name }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->doctor?->name }}</td>
                    <td class="py-3">
                        @php $badge = $statusBadge[$record->status] ?? $statusBadge['draft']; @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    </td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('medical-records.show', $record) }}" class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Detail</a>
                            @if($record->status === 'draft')
                                <a href="{{ route('medical-records.edit', $record) }}" class="text-sm font-medium text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300">Edit</a>
                            @endif
                            @if($record->appointment)
                                <a href="{{ route('patients.medical-history', $record->appointment->patient_id) }}" class="text-sm font-medium text-success-600 hover:text-success-800 dark:text-success-400 dark:hover:text-success-300">Riwayat</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data rekam medis.</td></tr>
                @endforelse
        </x-table>
    <div class="mt-4">
        {{ $records->links() }}
    </div>
</div>
@endsection
