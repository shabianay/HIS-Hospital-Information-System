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
        <form method="GET" action="{{ route('medicines.mutations') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
            <select name="type" class="w-full sm:w-40 border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <option value="">Semua Tipe</option>
                <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Stok Masuk</option>
                <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Stok Keluar</option>
            </select>
            <select name="medicine_id" class="w-full sm:w-56 border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <option value="">Semua Obat</option>
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}" {{ request('medicine_id') == $medicine->id ? 'selected' : '' }}>{{ $medicine->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <x-primary-button type="submit">Filter</x-primary-button>
        </form>

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