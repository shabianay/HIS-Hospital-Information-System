@extends('layouts.app')
@section('title', 'Antrian Online')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Antrian Online</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('online-registrations.portal') }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Buka Portal</a>
            <a href="{{ route('online-registrations.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Hari Ini</p>
            <p class="mt-1 text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $summary['today'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Menunggu</p>
            <p class="mt-1 text-xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['waiting'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Selesai Hari Ini</p>
            <p class="mt-1 text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ $summary['completed'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-filter-form action="{{ route('online-registrations.index') }}" applyLabel="Filter" cols="3">
            <div>
                <x-input-label for="status">Status</x-input-label>
                <x-select name="status" id="status">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\OnlineRegistration::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="registration_date">Tanggal Registrasi</x-input-label>
                <x-text-input type="date" name="registration_date" id="registration_date" value="{{ request('registration_date') }}" />
            </div>
        </x-filter-form>

        <x-table placeholder="Cari no. registrasi / pasien...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Registrasi</th>
                    <th class="pb-4 px-4 font-semibold">Tanggal</th>
                    <th class="pb-4 px-4 font-semibold">No. Antrian</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Poli</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($registrations as $reg)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $reg->registration_number }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $reg->registration_date?->format('d/m/Y') }}</td>
                <td class="py-4 px-4 text-sm font-mono font-bold text-primary-600 dark:text-primary-400">{{ $reg->queue_number }}</td>
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $reg->patient_name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\OnlineRegistration::POLIS[$reg->poli] ?? $reg->poli }}</td>
                <td class="py-4 px-4 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        @if($reg->status === 'completed') bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300
                        @elseif($reg->status === 'checked_in') bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300
                        @elseif($reg->status === 'cancelled') bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300
                        @else bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300 @endif">
                        {{ \App\Models\OnlineRegistration::STATUSES[$reg->status] ?? $reg->status }}
                    </span>
                </td>
                <td class="py-4 px-4">
                    <div class="flex items-center gap-2">
                        @if($reg->status === 'registered')
                            <form method="POST" action="{{ route('online-registrations.checkin', $reg) }}">
                                @csrf
                                <x-action-link type="submit" variant="primary">Konfirmasi Datang</x-action-link>
                            </form>
                        @endif
                        @if(in_array($reg->status, ['registered', 'checked_in']))
                            <form method="POST" action="{{ route('online-registrations.complete', $reg) }}">
                                @csrf
                                <x-action-link type="submit" variant="success">Selesai</x-action-link>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada antrian online.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection