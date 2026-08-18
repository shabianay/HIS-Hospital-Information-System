@extends('layouts.app')
@section('title', 'Pendaftaran Janji Temu')
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
            'cancelled' => [
                'label' => 'Dibatalkan',
                'color' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400',
            ],
        ];
    @endphp

    <div
        class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-2xl shadow-glass-sm overflow-hidden">
        <div class="p-6 md:p-8">
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Pendaftaran Janji Temu</h3>
                <div class="flex items-center gap-3">
                    <a href="{{ route('appointments.index.csv', request()->query()) }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all duration-200">Export CSV</a>
                    <a href="{{ route('appointments.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Daftar Janji Temu
                    </a>
            </div>

            <form method="GET" action="{{ route('appointments.index') }}"
                class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-6 p-6 bg-secondary-50 dark:bg-secondary-900/30 rounded-2xl border border-border-light dark:border-border-dark">
                <div>
                    <x-input-label for="date" value="Filter Tanggal" />
                    <x-text-input type="date" name="date" id="date" value="{{ request('date', now()->toDateString()) }}" />
                </div>
                <div>
                    <x-input-label for="poli_id" value="Filter Poli" />
                    <select name="poli_id" id="poli_id"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Semua Poli</option>
                        @foreach ($polis as $poli)
                            <option value="{{ $poli->id }}" {{ request('poli_id') == $poli->id ? 'selected' : '' }}>
                                {{ $poli->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="status" value="Filter Status" />
                    <select name="status" id="status"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Semua Status</option>
                        @foreach ($statuses as $key => $st)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $st['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-xl transition-colors shadow-glass-sm">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('appointments.index') }}"
                        class="ml-2 px-6 py-3 bg-white dark:bg-secondary-800 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark font-semibold text-sm rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors shadow-glass-sm">
                        Reset
                    </a>
                </div>
            </form>

            <x-table placeholder="Cari pasien / dokter / poli..." class="overflow-hidden">
                <x-slot name="head">
                    <tr
                        class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                        <th class="pb-4 px-4 font-semibold">No. Antrian</th>
                        <th class="pb-4 px-4 font-semibold">Tanggal</th>
                        <th class="pb-4 px-4 font-semibold">Pasien</th>
                        <th class="pb-4 px-4 font-semibold">Poli</th>
                        <th class="pb-4 px-4 font-semibold">Dokter</th>
                        <th class="pb-4 px-4 font-semibold">Status</th>
                        <th class="pb-4 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </x-slot>
                @forelse($appointments as $appointment)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())"
                        class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td
                            class="py-4 px-4 font-mono text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                            {{ $appointment->queue_number }}</td>
                        <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                            {{ $appointment->appointment_date?->format('d/m/Y') }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            {{ $appointment->patient?->name }}</td>
                        <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                            {{ $appointment->poli?->name }}</td>
                        <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                            {{ $appointment->doctor?->name }}</td>
                        <td class="py-4 px-4">
                            @php $st = $statuses[$appointment->status] ?? $statuses['waiting']; @endphp
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['color'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <a href="{{ route('appointments.show', $appointment) }}"
                                class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr x-show="!search" data-search-row>
                        <td colspan="7"
                            class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada
                            janji temu.</td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-6">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>
@endsection
