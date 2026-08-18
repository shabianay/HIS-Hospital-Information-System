@extends('layouts.app')
@section('title', 'Detail Obat')
@section('content')
<div class="w-full space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Detail Informasi Obat</h2>
            <div class="flex gap-3">
                <x-secondary-button href="{{ route('medicines.edit', $medicine) }}">Edit</x-secondary-button>
                <x-secondary-button href="{{ route('medicines.index') }}">Kembali</x-secondary-button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 pb-8 border-b border-border-light dark:border-border-dark">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Nama Obat</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicine->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Nama Generik</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicine->generic_name ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Kategori</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicine->category ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Satuan</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicine->unit }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Harga Beli / Jual</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($medicine->buy_price, 0, ',', '.') }} / Rp {{ number_format($medicine->sell_price, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Total Stok</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $totalStock }} {{ $medicine->unit }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Stok Minimum</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicine->minimum_stock }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Status</span>
                @if($medicine->is_active)
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Aktif</span>
                @else
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800">Nonaktif</span>
                @endif
            </div>
        </div>

        <div class="pt-8">
            <h3 class="mb-4 text-lg font-semibold text-primary-800 dark:text-primary-400">Batch Stok & Kedaluwarsa</h3>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">No. Batch</th>
                        <th class="pb-3 font-semibold">Jumlah</th>
                        <th class="pb-3 font-semibold">Tanggal Kedaluwarsa</th>
                    </tr>
                </x-slot>
                @forelse($medicine->stocks as $stock)
                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $stock->batch_number ?: '-' }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $stock->quantity }} {{ $medicine->unit }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $stock->expiry_date?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada stok.</td></tr>
                @endforelse
            </x-table>
        </div>
        </div>
    </div>
</div>
@endsection
