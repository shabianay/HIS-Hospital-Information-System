@extends('layouts.app')
@section('title', 'Rekomendasi Pembelian Stok')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Rekomendasi Pembelian Stok</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('medicines.stock') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Manajemen Stok</a>
            <a href="{{ route('medicines.mutations') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Riwayat Mutasi</a>
        </div>
    </div>

    @if($lowStock->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <p class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Obat di Bawah Minimum</p>
                <p class="mt-1 text-3xl font-bold text-danger-600 dark:text-danger-400">{{ $lowStock->count() }}</p>
            </div>
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <p class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Total Unit Rekomendasi</p>
                <p class="mt-1 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ $lowStock->sum('suggested_quantity') }}</p>
            </div>
            <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <p class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Estimasi Biaya Pembelian</p>
                <p class="mt-1 text-3xl font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($totalSuggestedCost, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
            <div class="mb-4 rounded-xl border border-warning-200 dark:border-warning-800 bg-warning-50 dark:bg-warning-900/20 p-4 text-sm">
                <span class="font-medium text-text-primary-light dark:text-text-primary-dark">Rekomendasi dihitung sebagai: <strong>(Batas minimum − Stok saat ini) + 10 unit</strong>.</span>
                <span class="text-text-secondary-light dark:text-text-secondary-dark"> Stok dapat langsung ditambahkan pada halaman Manajemen Stok.</span>
            </div>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                        <th class="pb-4 px-4 font-semibold">Nama Obat</th>
                        <th class="pb-4 px-4 font-semibold">Stok Saat Ini</th>
                        <th class="pb-4 px-4 font-semibold">Batas Minimal</th>
                        <th class="pb-4 px-4 font-semibold">Rekomendasi Pembelian</th>
                        <th class="pb-4 px-4 font-semibold">Harga Beli</th>
                        <th class="pb-4 px-4 font-semibold">Estimasi Biaya</th>
                    </tr>
                </x-slot>
                @foreach($lowStock as $item)
                    <tr class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-semibold">{{ $item->name }}</td>
                        <td class="py-4 px-4 text-sm text-danger-600 dark:text-danger-400 font-semibold">{{ $item->total_stock }} {{ $item->unit }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-bold text-primary-700 dark:text-primary-300">{{ $item->suggested_quantity }} {{ $item->unit }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format((float) $item->buy_price, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($item->estimated_cost, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </x-table>
            <div class="mt-4 flex items-center justify-end gap-2 border-t border-border-light dark:border-border-dark pt-4 text-sm">
                <span class="font-semibold text-text-secondary-light dark:text-text-secondary-dark">Total Estimasi Biaya:</span>
                <span class="text-lg font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($totalSuggestedCost, 0, ',', '.') }}</span>
            </div>
        </div>
    @else
        <div class="bg-surface-light dark:bg-surface-dark p-10 rounded-2xl border border-border-light dark:border-border-dark text-center shadow-glass-sm">
            <p class="font-semibold text-text-primary-light dark:text-text-primary-dark">Tidak ada obat dengan stok di bawah batas minimum.</p>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">Semua stok obat dalam kondisi sehat.</p>
        </div>
    @endif
</div>
@endsection