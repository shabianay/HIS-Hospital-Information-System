@extends('layouts.app')
@section('title', 'Antrian Resep')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Antrian Resep Obat</h2>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                {{ $counts['total'] }} resep menunggu · {{ $counts['medicines'] }} obat berbeda · {{ $counts['patients'] }} pasien
            </p>
        </div>
        <a href="{{ route('medicines.index') }}"
            class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
            Manajemen Obat
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Resep Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $counts['total'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Obat Berbeda</p>
            <p class="mt-1 text-2xl font-bold text-info-600 dark:text-info-400">{{ $counts['medicines'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Pasien Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $counts['patients'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari pasien / obat / resep..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Waktu</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Obat</th>
                    <th class="pb-4 px-4 font-semibold">Jumlah</th>
                    <th class="pb-4 px-4 font-semibold">Dosis</th>
                    <th class="pb-4 px-4 font-semibold">Aturan</th>
                    <th class="pb-4 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </x-slot>
            @forelse($prescriptions as $prescription)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $prescription->created_at?->format('d/m/Y H:i') }}</td>
                <td class="py-4 px-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                    {{ $prescription->medicalRecord?->patient?->name ?? '-' }}
                    @if($prescription->medicalRecord?->patient?->rm_number)
                        <span class="block text-xs font-mono text-text-secondary-light dark:text-text-secondary-dark">{{ $prescription->medicalRecord->patient->rm_number }}</span>
                    @endif
                </td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $prescription->medicine?->name ?? '(Dihapus)' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $prescription->quantity }} {{ $prescription->medicine?->unit }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $prescription->dosage }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                    {{ $prescription->frequency }}{{ $prescription->duration ? ' · ' . $prescription->duration : '' }}
                    @if($prescription->instructions)
                        <span class="block text-xs text-text-secondary-light dark:text-text-secondary-dark">{{ $prescription->instructions }}</span>
                    @endif
                </td>
                <td class="py-4 px-4 text-right">
                    <form action="{{ route('prescriptions.dispense', $prescription) }}" method="POST" onsubmit="return confirm('Dispensasi resep {{ $prescription->medicine?->name }} dan kurangi stok?')">
                        @csrf
                        <x-action-link type="submit" variant="primary-solid">Dispensasi</x-action-link>
                    </form>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row>
                <td colspan="7" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada resep menunggu dispense.</td>
            </tr>
            @endforelse
        </x-table>
    </div>
</div>
@endsection