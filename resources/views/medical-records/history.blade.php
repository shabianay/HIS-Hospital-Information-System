@extends('layouts.app')
@section('title', 'Riwayat Medis Pasien')
@section('content')
@php
    $statusBadge = [
        'draft' => ['label' => 'Draft', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800'],
        'finalized' => ['label' => 'Final', 'class' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800'],
    ];
@endphp
<div class="space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Riwayat Medis Pasien</h3>
            <div class="flex items-center gap-3">
                <a href="{{ route('patients.medical-history.pdf', $patient) }}" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-5 py-2.5 text-sm font-semibold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all duration-200">Cetak PDF</a>
                <x-secondary-button href="{{ route('patients.show', $patient) }}">Kembali ke Profil</x-secondary-button>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Nama</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">NIK</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->nik }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Jenis Kelamin</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari dokter / poli / diagnosis..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Tanggal Kunjungan</th>
                    <th class="pb-3 font-semibold">Dokter</th>
                    <th class="pb-3 font-semibold">Poli</th>
                    <th class="pb-3 font-semibold">Lab</th>
                    <th class="pb-3 font-semibold">Diagnosis</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($records as $record)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->appointment_date?->format('d/m/Y') }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->doctor?->name }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $record->appointment?->poli?->name }}</td>
                        <td class="py-3">
                            @php
                                $hasLab = $record->appointment?->labRequests?->isNotEmpty();
                                $hasPendingLab = $hasLab && $record->appointment->labRequests->where('status', '!=', 'completed')->isNotEmpty();
                                $hasAbnormal = $hasLab && $record->appointment->labRequests->flatMap->items->contains('result_status', 'abnormal');
                            @endphp
                            @if(!$hasLab)
                                <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">-</span>
                            @elseif($hasAbnormal)
                                <span class="inline-flex items-center rounded-full bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 px-3 py-1 text-xs font-semibold border border-danger-200 dark:border-danger-800">Abnormal</span>
                            @elseif($hasPendingLab)
                                <span class="inline-flex items-center rounded-full bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 px-3 py-1 text-xs font-semibold border border-warning-200 dark:border-warning-800">Proses</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 px-3 py-1 text-xs font-semibold border border-success-200 dark:border-success-800">Selesai</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @foreach($record->diagnoses as $diag)
                                <span class="mr-1 inline-block rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-semibold text-primary-800 dark:text-primary-400 border border-primary-200 dark:border-primary-800">{{ $diag->icd_code }} {{ $diag->description }}</span>
                            @endforeach
                        </td>
                        <td class="py-3">
                            @php $badge = $statusBadge[$record->status] ?? $statusBadge['draft']; @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                        </td>
                        <td class="py-3">
                            <x-action-link href="{{ route('medical-records.show', $record) }}" variant="primary">Detail EMR</x-action-link>
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada rekam medis.</td></tr>
                    @endforelse
            </x-table>
        <div class="mt-4">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
