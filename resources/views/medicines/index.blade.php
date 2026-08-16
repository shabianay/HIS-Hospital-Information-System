@extends('layouts.app')
@section('title', 'Inventaris Obat (Farmasi)')
@section('content')
<div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
        <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Inventaris Obat</h3>
        <div class="flex gap-2">
            <a href="{{ route('medicines.stock') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Manajemen Stok</a>
            <a href="{{ route('medicines.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Obat
            </a>
        </div>
    </div>

    <x-table  placeholder="Cari nama / nama generik obat..." class="overflow-hidden">
        <x-slot name="head">
            <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                <th class="pb-4 px-4 font-semibold">Nama Obat</th>
                <th class="pb-4 px-4 font-semibold">Nama Generik</th>
                <th class="pb-4 px-4 font-semibold">Kategori</th>
                <th class="pb-4 px-4 font-semibold">Satuan</th>
                <th class="pb-4 px-4 font-semibold">Harga Beli</th>
                <th class="pb-4 px-4 font-semibold">Harga Jual</th>
                <th class="pb-4 px-4 font-semibold">Total Stok</th>
                <th class="pb-4 px-4 font-semibold">Status</th>
                <th class="pb-4 px-4 font-semibold">Aksi</th>
            </tr>
        </x-slot>
        @forelse($medicines as $medicine)
                @php
                    $totalStock = $medicine->total_stock ?? 0;
                    if ($totalStock <= 0) {
                        $stockStatus = ['label' => 'Habis', 'color' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800'];
                    } elseif ($totalStock < $medicine->minimum_stock) {
                        $stockStatus = ['label' => 'Menipis', 'color' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800'];
                    } else {
                        $stockStatus = ['label' => 'Tersedia', 'color' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800'];
                    }
                @endphp
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-semibold">{{ $medicine->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $medicine->generic_name ?: '-' }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $medicine->category ?: '-' }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $medicine->unit }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($medicine->buy_price, 0, ',', '.') }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($medicine->sell_price, 0, ',', '.') }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $totalStock }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $stockStatus['color'] }}">{{ $stockStatus['label'] }}</span>
                    </td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('medicines.show', $medicine) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Detail</a>
                            <a href="{{ route('medicines.edit', $medicine) }}" class="inline-flex items-center justify-center px-3 py-1.5 bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-800 rounded-lg text-xs font-medium text-warning-700 dark:text-warning-400 hover:bg-warning-100 dark:hover:bg-warning-800 transition-all">Edit</a>
                            <form action="{{ route('medicines.destroy', $medicine) }}" method="POST" onsubmit="return confirm('Hapus obat ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-800 rounded-lg text-xs font-medium text-danger-700 dark:text-danger-400 hover:bg-danger-100 dark:hover:bg-danger-800 transition-all">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr x-show="!search" data-search-row><td colspan="9" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data obat.</td></tr>
                @endforelse
    </x-table>

    <div class="mt-6">
        {{ $medicines->links() }}
    </div>
</div>
@endsection