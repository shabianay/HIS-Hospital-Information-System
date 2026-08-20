@extends('layouts.app')
@section('title', 'IGD & Triase')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">IGD & Triase</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('emergency.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <a href="{{ route('emergency.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Daftar Pasien IGD</a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Menunggu Triase</p>
            <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['waiting'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Dalam Perawatan</p>
            <p class="mt-1 text-2xl font-bold text-info-600 dark:text-info-400">{{ $summary['treatment'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Dirujuk Rawat Inap</p>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $summary['admitted'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Triase Merah Aktif</p>
            <p class="mt-1 text-2xl font-bold {{ $summary['red'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-text-primary-light dark:text-text-primary-dark' }}">{{ $summary['red'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-filter-form action="{{ route('emergency.index') }}" applyLabel="Filter">
            <div>
                <x-input-label for="status">Status</x-input-label>
                <x-select name="status" id="status">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\EmergencyVisit::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="triage_level">Triase</x-input-label>
                <x-select name="triage_level" id="triage_level">
                    <option value="">Semua Triase</option>
                    @foreach(\App\Models\EmergencyVisit::TRIAGE_LEVELS as $val => $label)
                        <option value="{{ $val }}" {{ request('triage_level') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="date">Tanggal</x-input-label>
                <x-text-input type="date" name="date" id="date" value="{{ request('date') }}" />
            </div>
        </x-filter-form>

        <x-table placeholder="Cari pasien / no. kunjungan...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Kunjungan</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Keluhan</th>
                    <th class="pb-4 px-4 font-semibold">Triase</th>
                    <th class="pb-4 px-4 font-semibold">Dokter</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($visits as $visit)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $visit->visit_number }}</td>
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $visit->patient?->name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \Illuminate\Support\Str::limit($visit->chief_complaint, 40) }}</td>
                <td class="py-4 px-4">
                    @php
                        $triageBadges = [
                            'red' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
                            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
                            'green' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                            'black' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                        ];
                        $triageLabels = ['red' => 'Merah', 'yellow' => 'Kuning', 'green' => 'Hijau', 'black' => 'Hitam'];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $triageBadges[$visit->triage_level] ?? '' }}">{{ $triageLabels[$visit->triage_level] ?? $visit->triage_level }}</span>
                </td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $visit->doctor?->name ?? '-' }}</td>
                <td class="py-4 px-4">
                    @php
                        $statusBadges = [
                            'waiting' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
                            'in_triage' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
                            'treatment' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
                            'observation' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border border-info-200 dark:border-info-800',
                            'admitted' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                            'discharged' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                            'referred' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border border-info-200 dark:border-info-800',
                            'deceased' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadges[$visit->status] ?? '' }}">{{ \App\Models\EmergencyVisit::STATUSES[$visit->status] ?? $visit->status }}</span>
                </td>
                <td class="py-4 px-4">
                    <x-action-link href="{{ route('emergency.show', $visit) }}">Detail</x-action-link>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada kunjungan IGD.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $visits->links() }}
        </div>
    </div>
</div>
@endsection