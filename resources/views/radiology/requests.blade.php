@extends('layouts.app')
@section('title', 'Permintaan Radiologi')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Permintaan Radiologi</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('radiology.requests.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <a href="{{ route('radiology.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Buat Permintaan</a>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Diproses</p>
            <p class="mt-1 text-2xl font-bold text-info-600 dark:text-info-400">{{ $summary['in_progress'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Selesai</p>
            <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $summary['completed'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Urgent Menunggu</p>
            <p class="mt-1 text-2xl font-bold {{ $summary['urgent'] > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-text-primary-light dark:text-text-primary-dark' }}">{{ $summary['urgent'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-filter-form action="{{ route('radiology.requests') }}" applyLabel="Filter">
            <div>
                <x-input-label for="status">Status</x-input-label>
                <x-select name="status" id="status">
                    <option value="">Semua Status</option>
                    @foreach(['pending' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="date">Tanggal</x-input-label>
                <x-text-input type="date" name="date" id="date" value="{{ request('date') }}" />
            </div>
        </x-filter-form>

        <x-table placeholder="Cari pasien / no. antrian...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">ID</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Antrian</th>
                    <th class="pb-4 px-4 font-semibold">Jumlah Pemeriksaan</th>
                    <th class="pb-4 px-4 font-semibold">Perujuk</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($requests as $req)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">#{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $req->patient?->name }}
                    @if($req->is_urgent)<span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400">PRIORITAS</span>@endif
                </td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $req->appointment?->queue_number ?? '-' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $req->items()->count() }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $req->doctor?->name ?? '-' }}</td>
                <td class="py-4 px-4">
                    @php
                        $badges = [
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
                            'in_progress' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
                            'completed' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                            'cancelled' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
                        ];
                        $labels = ['pending' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badges[$req->status] }}">{{ $labels[$req->status] }}</span>
                </td>
                <td class="py-4 px-4">
                    <x-action-link href="{{ route('radiology.requests.show', $req) }}" variant="primary">Proses</x-action-link>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada permintaan radiologi.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection