@extends('layouts.app')
@section('title', 'Riwayat Mutasi Stok')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Riwayat Mutasi Stok</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('medicines.mutations.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-filter-form action="{{ route('medicines.mutations') }}" applyLabel="Filter" cols="4">
            <div>
                <x-input-label for="type">Tipe</x-input-label>
                <x-select name="type" id="type">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stok Masuk</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stok Keluar</option>
                    <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Retur / Pengembalian</option>
                </x-select>
            </div>
            <div>
                <x-input-label for="medicine_id">Obat</x-input-label>
                <x-select name="medicine_id" id="medicine_id">
                    <option value="">Semua Obat</option>
                    @foreach($medicines as $medicine)
                        <option value="{{ $medicine->id }}" {{ request('medicine_id') == $medicine->id ? 'selected' : '' }}>{{ $medicine->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-input-label for="date">Tanggal</x-input-label>
                <x-text-input type="date" name="date" id="date" value="{{ request('date') }}" />
            </div>
        </x-filter-form>

        <x-table placeholder="Cari obat / referensi...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">Waktu</th>
                    <th class="pb-4 px-4 font-semibold">Obat</th>
                    <th class="pb-4 px-4 font-semibold">Tipe</th>
                    <th class="pb-4 px-4 font-semibold">Jumlah</th>
                    <th class="pb-4 px-4 font-semibold">Referensi</th>
                    <th class="pb-4 px-4 font-semibold">Catatan</th>
                </tr>
            </x-slot>
            @forelse($mutations as $mutation)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $mutation->created_at?->format('d/m/Y H:i:s') }}</td>
                <td class="py-4 px-4 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $mutation->medicine?->name ?? '(Dihapus)' }}</td>
                <td class="py-4 px-4">
                    @if($mutation->type === 'in')
                        <span class="inline-flex items-center rounded-full bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 px-3 py-1 text-xs font-semibold border border-success-200 dark:border-success-800">Masuk</span>
                    @elseif($mutation->type === 'return')
                        <span class="inline-flex items-center rounded-full bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 px-3 py-1 text-xs font-semibold border border-warning-200 dark:border-warning-800">Retur</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 px-3 py-1 text-xs font-semibold border border-danger-200 dark:border-danger-800">Keluar</span>
                    @endif
                </td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $mutation->type === 'in' ? '+' : '-' }}{{ $mutation->quantity }}</td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $mutation->reference ?: '-' }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $mutation->notes ?: '-' }}</td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada mutasi stok.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $mutations->links() }}
        </div>
    </div>
</div>
@endsection