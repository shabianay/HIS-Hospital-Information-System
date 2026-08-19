@extends('layouts.app')
@section('title', 'Refund / Pengembalian Dana')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Refund / Pengembalian Dana</h2>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('refunds.csv', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2.5 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Export CSV</a>
            <a href="{{ route('refunds.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Proses Refund</a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Total Refund</p>
            <p class="mt-1 text-xl font-bold text-danger-600 dark:text-danger-400">Rp {{ number_format($summary['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-5 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Jumlah Transaksi</p>
            <p class="mt-1 text-xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $summary['count'] }}</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <form method="GET" action="{{ route('refunds.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <span class="self-center text-sm text-text-secondary-light dark:text-text-secondary-dark">s/d</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
            <x-primary-button type="submit">Filter</x-primary-button>
        </form>

        <x-table placeholder="Cari no. refund / pasien...">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">No. Refund</th>
                    <th class="pb-4 px-4 font-semibold">Tanggal</th>
                    <th class="pb-4 px-4 font-semibold">No. Invoice</th>
                    <th class="pb-4 px-4 font-semibold">Pasien</th>
                    <th class="pb-4 px-4 font-semibold">Alasan</th>
                    <th class="pb-4 px-4 font-semibold">Jumlah</th>
                </tr>
            </x-slot>
            @forelse($refunds as $refund)
            <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $refund->refund_number }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $refund->refunded_at?->format('d/m/Y H:i') }}</td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">{{ $refund->billing?->invoice_number ?? '-' }}</td>
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $refund->patient?->name }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\Refund::REASONS[$refund->reason] ?? $refund->reason }}</td>
                <td class="py-4 px-4 text-sm font-mono text-danger-600 dark:text-danger-400">Rp {{ number_format($refund->amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada transaksi refund.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6">
            {{ $refunds->links() }}
        </div>
    </div>
</div>
@endsection