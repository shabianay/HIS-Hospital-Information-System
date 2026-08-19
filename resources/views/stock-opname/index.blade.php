@extends('layouts.app')
@section('title', 'Stock Opname')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Stock Opname</h2>
        <a href="{{ route('stock-opname.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Buat Stock Opname</a>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Opname</p>
            <p class="mt-1 text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $summary['count'] }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Dengan Selisih</p>
            <p class="mt-1 text-xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['discrepancies'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari no. opname / status...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Opname</th>
                    <th class="pb-4 px-4 font-semibold">Tanggal</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Total Item</th>
                    <th class="pb-4 px-4 font-semibold">Selisih</th>
                    <th class="pb-4 px-4 font-semibold">Dibuat Oleh</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($opnames as $opname)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $opname->opname_number }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $opname->opname_date?->format('d/m/Y') }}</td>
                <td class="py-4 px-4 text-sm">
                    @if($opname->status === 'approved')
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300">Disetujui</span>
                    @else
                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300">Draft</span>
                    @endif
                </td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $opname->items_count }}</td>
                <td class="py-4 px-4 text-sm font-mono {{ $opname->total_difference != 0 ? 'text-danger-600 dark:text-danger-400' : 'text-text-secondary-light dark:text-text-secondary-dark' }}">{{ $opname->total_difference }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $opname->created_by_name ?? $opname->createdBy?->name ?? '-' }}</td>
                <td class="py-4 px-4">
                    <a href="{{ route('stock-opname.show', $opname) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">Detail</a>
                </td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="7" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada stock opname.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $opnames->links() }}
        </div>
    </div>
</div>
@endsection