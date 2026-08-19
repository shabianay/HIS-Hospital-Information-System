@extends('layouts.app')
@section('title', 'Jadwal Operasi')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Jadwal Operasi (OK)</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('surgeries.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <a href="{{ route('surgeries.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Buat Jadwal Operasi</a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Terjadwal</p>
            <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['scheduled'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Berlangsung</p>
            <p class="mt-1 text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $summary['in_progress'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Selesai</p>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $summary['completed'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Bedah Mayor Aktif</p>
            <p class="mt-1 text-2xl font-bold {{ $summary['major'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-text-primary-light dark:text-text-primary-dark' }}">{{ $summary['major'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <form method="GET" action="{{ route('surgeries.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
            <select name="status" class="w-full sm:w-52 border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Surgery::STATUSES as $val => $label)
                    <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <x-primary-button type="submit">Filter</x-primary-button>
        </form>

        <x-table placeholder="Cari pasien / no. operasi...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Operasi</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Prosedur</th>
                    <th class="pb-4 px-4 font-semibold">Jenis</th>
                    <th class="pb-4 px-4 font-semibold">Operator</th>
                    <th class="pb-4 px-4 font-semibold">Jadwal</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($surgeries as $surgery)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $surgery->surgery_number }}</td>
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $surgery->patient?->name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $surgery->procedure_name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\Surgery::TYPES[$surgery->surgery_type] ?? $surgery->surgery_type }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $surgery->doctor?->name ?? '-' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $surgery->scheduled_at?->format('d/m/Y H:i') }}</td>
                <td class="py-4 px-4">
                    @php
                        $badges = [
                            'scheduled' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800',
                            'in_progress' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
                            'completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                            'cancelled' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badges[$surgery->status] }}">{{ \App\Models\Surgery::STATUSES[$surgery->status] ?? $surgery->status }}</span>
                </td>
                <td class="py-4 px-4">
                    <a href="{{ route('surgeries.show', $surgery) }}" class="text-sm font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">Detail</a>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="8" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada jadwal operasi.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $surgeries->links() }}
        </div>
    </div>
</div>
@endsection