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

        $total = $appointments->total();
        $counts = [
            'waiting' => $appointments->where('status', 'waiting')->count(),
            'in_progress' => $appointments->where('status', 'in_progress')->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Pendaftaran Janji Temu</h2>
                <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                    {{ \Carbon\Carbon::parse(request('date', now()->toDateString()))->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('appointments.index.csv', request()->query()) }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                    Export CSV
                </a>
                <a href="{{ route('appointments.create') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Daftar Janji Temu
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-warning-100 dark:bg-warning-900/30 rounded-2xl px-5 py-4 border border-warning-200 dark:border-warning-800/40">
                <p class="text-xs font-semibold uppercase tracking-wider text-warning-800 dark:text-warning-400">Menunggu</p>
                <p class="mt-1 text-2xl font-bold text-warning-900 dark:text-warning-300">{{ $counts['waiting'] }}</p>
            </div>
            <div
                class="bg-info-100 dark:bg-info-900/30 rounded-2xl px-5 py-4 border border-info-200 dark:border-info-800/40">
                <p class="text-xs font-semibold uppercase tracking-wider text-info-800 dark:text-info-400">Sedang Diperiksa</p>
                <p class="mt-1 text-2xl font-bold text-info-900 dark:text-info-300">{{ $counts['in_progress'] }}</p>
            </div>
            <div
                class="bg-success-100 dark:bg-success-900/30 rounded-2xl px-5 py-4 border border-success-200 dark:border-success-800/40">
                <p class="text-xs font-semibold uppercase tracking-wider text-success-800 dark:text-success-400">Selesai</p>
                <p class="mt-1 text-2xl font-bold text-success-900 dark:text-success-300">{{ $counts['completed'] }}</p>
            </div>
            <div
                class="bg-danger-100 dark:bg-danger-900/30 rounded-2xl px-5 py-4 border border-danger-200 dark:border-danger-800/40">
                <p class="text-xs font-semibold uppercase tracking-wider text-danger-800 dark:text-danger-400">Dibatalkan</p>
                <p class="mt-1 text-2xl font-bold text-danger-900 dark:text-danger-300">{{ $counts['cancelled'] }}</p>
            </div>
        </div>

        <x-filter-form action="{{ route('appointments.index') }}">
            <div>
                <x-input-label for="date" value="Filter Tanggal" />
                <x-text-input type="date" name="date" id="date" value="{{ request('date', now()->toDateString()) }}" />
            </div>
            <div>
                <x-input-label for="poli_id" value="Filter Poli" />
                <x-select name="poli_id" id="poli_id">
                    <option value="">Semua Poli</option>
                    @foreach ($polis as $poli)
                        <option value="{{ $poli->id }}" {{ request('poli_id') == $poli->id ? 'selected' : '' }}>
                            {{ $poli->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="status" value="Filter Status" />
                <x-select name="status" id="status">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $key => $st)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $st['label'] }}</option>
                    @endforeach
                </x-select>
            </div>
        </x-filter-form>

        <div
            class="bg-surface-light dark:bg-surface-dark p-6 md:p-8 border border-border-light dark:border-border-dark rounded-2xl shadow-glass-sm">
            <x-table placeholder="Cari pasien / dokter / poli...">
                <x-slot name="head">
                    <tr
                        class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                        <th class="pb-4 pt-1 px-4 font-semibold">No. Antrian</th>
                        <th class="pb-4 pt-1 px-4 font-semibold">Tanggal</th>
                        <th class="pb-4 pt-1 px-4 font-semibold">Pasien</th>
                        <th class="pb-4 pt-1 px-4 font-semibold">Poli</th>
                        <th class="pb-4 pt-1 px-4 font-semibold">Dokter</th>
                        <th class="pb-4 pt-1 px-4 font-semibold">Status</th>
                        <th class="pb-4 pt-1 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </x-slot>
                @forelse($appointments as $appointment)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())"
                        class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td
                            class="py-4 px-4 font-mono text-sm font-medium text-text-primary-light dark:text-text-primary-dark">
                            {{ $appointment->queue_number }}</td>
                        <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark whitespace-nowrap">
                            {{ $appointment->appointment_date?->format('d/m/Y') }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            {{ $appointment->patient?->name }}</td>
                        <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                            {{ $appointment->poli?->name }}</td>
                        <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                            {{ $appointment->doctor?->name }}</td>
                        <td class="py-4 px-4 whitespace-nowrap">
                            @php $st = $statuses[$appointment->status] ?? $statuses['waiting']; @endphp
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['color'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td class="py-4 px-4 text-right whitespace-nowrap">
                            <x-action-link href="{{ route('appointments.show', $appointment) }}">Detail</x-action-link>
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

            <div class="mt-6 pt-6 border-t border-border-light dark:border-border-dark">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>
@endsection
