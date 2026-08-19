@extends('layouts.app')
@section('title', 'Rawat Inap')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Rawat Inap</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admissions.index.csv') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Export CSV</a>
            <a href="{{ route('admissions.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrasi Rawat Inap
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-3">
        <div class="rounded-xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-4">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Sedang Dirawat</div>
            <div class="mt-1 text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $counts['admitted'] }}</div>
        </div>
        <div class="rounded-xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-4">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Masuk Hari Ini</div>
            <div class="mt-1 text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $counts['today'] }}</div>
        </div>
        <div class="rounded-xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-4">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Selesai (Pulang)</div>
            <div class="mt-1 text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $counts['discharged'] }}</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admissions.index') }}" class="mb-6 flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
        <select name="status" class="w-full sm:w-auto border border-border-light bg-surface-light px-4 py-2.5 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <option value="">Semua Status</option>
            @foreach(\App\Models\Admission::STATUSES as $key => $label)
                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}" class="w-full sm:w-auto border border-border-light bg-surface-light px-4 py-2.5 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
        <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-all duration-200">Filter</button>
        @if(request('status') || request('date'))
            <a href="{{ route('admissions.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-sm font-semibold text-text-primary-light dark:text-text-primary-dark rounded-xl transition-all duration-200">Reset</a>
        @endif
    </form>

    <x-table placeholder="Cari pasien / no. registrasi..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">No. Registrasi</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Dokter</th>
                    <th class="pb-4 px-4 font-semibold">Kamar / TT</th>
                    <th class="pb-4 px-4 font-semibold">Masuk</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($admissions as $admission)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $admission->admission_number }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->patient?->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->doctor?->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->room?->name }} / TT {{ $admission->bed?->bed_number }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $admission->admitted_at?->format('d/m/Y H:i') }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        @php
                            $statusClass = [
                                'admitted' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                                'discharged' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                                'cancelled' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
                            ][$admission->status] ?? 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800';
                        @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">{{ \App\Models\Admission::STATUSES[$admission->status] ?? $admission->status }}</span>
                    </td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        <a href="{{ route('admissions.show', $admission) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="7" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data rawat inap.</td></tr>
                @endforelse
            </x-table>
    <div class="mt-6">
        {{ $admissions->links() }}
    </div>
</div>
@endsection