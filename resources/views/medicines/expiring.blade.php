@extends('layouts.app')
@section('title', 'Stok Mendekati Kedaluwarsa')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Stok Mendekati Kedaluwarsa</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('medicines.stock') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Manajemen Stok</a>
            <a href="{{ route('medicines.reorder') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Pembelian Stok</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Kedaluwarsa &lt; 60 Hari</p>
            <p class="mt-1 text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $expiringStocks->count() }} batch</p>
        </div>
        <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <p class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Sudah Kedaluwarsa</p>
            <p class="mt-1 text-3xl font-bold text-danger-600 dark:text-danger-400">{{ $expiredStocks->count() }} batch</p>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Mendekati Kedaluwarsa (60 hari)</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                        <th class="pb-4 px-4 font-semibold">Obat</th>
                        <th class="pb-4 px-4 font-semibold">Batch</th>
                        <th class="pb-4 px-4 font-semibold">Sisa Stok</th>
                        <th class="pb-4 px-4 font-semibold">Kedaluwarsa</th>
                        <th class="pb-4 px-4 font-semibold">Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expiringStocks as $stock)
                        <tr class="border-b border-border-light dark:border-border-dark last:border-0 hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                            <td class="py-4 px-4 font-medium text-text-primary-light dark:text-text-primary-dark">{{ $stock->medicine?->name ?? '-' }}</td>
                            <td class="py-4 px-4 text-text-primary-light dark:text-text-primary-dark">{{ $stock->batch_number ?: '-' }}</td>
                            <td class="py-4 px-4 text-text-primary-light dark:text-text-primary-dark">{{ $stock->quantity }} {{ $stock->medicine?->unit ?? '' }}</td>
                            <td class="py-4 px-4 text-warning-700 dark:text-warning-400">{{ $stock->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-4 px-4">
                                <span class="rounded-full bg-warning-100 dark:bg-warning-900/30 text-warning-800 dark:text-warning-400 px-2 py-0.5 text-xs font-semibold">{{ $stock->expiry_date?->diffInDays(now()) }} hari</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada batch yang mendekati kedaluwarsa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-danger-200 dark:border-danger-800 shadow-glass-sm overflow-hidden">
        <h3 class="mb-4 text-lg font-bold text-danger-700 dark:text-danger-400">Sudah Kedaluwarsa</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                        <th class="pb-4 px-4 font-semibold">Obat</th>
                        <th class="pb-4 px-4 font-semibold">Batch</th>
                        <th class="pb-4 px-4 font-semibold">Sisa Stok</th>
                        <th class="pb-4 px-4 font-semibold">Kedaluwarsa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expiredStocks as $stock)
                        <tr class="border-b border-border-light dark:border-border-dark last:border-0 hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                            <td class="py-4 px-4 font-medium text-text-primary-light dark:text-text-primary-dark">{{ $stock->medicine?->name ?? '-' }}</td>
                            <td class="py-4 px-4 text-text-primary-light dark:text-text-primary-dark">{{ $stock->batch_number ?: '-' }}</td>
                            <td class="py-4 px-4 text-danger-700 dark:text-danger-400">{{ $stock->quantity }} {{ $stock->medicine?->unit ?? '' }}</td>
                            <td class="py-4 px-4 text-danger-700 dark:text-danger-400">{{ $stock->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada stok kedaluwarsa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
