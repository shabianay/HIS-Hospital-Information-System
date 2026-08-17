@extends('layouts.app')
@section('title', 'Manajemen Stok Obat')
@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Manajemen Stok Obat</h2>
        <a href="{{ route('medicines.mutations') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Riwayat Mutasi</a>
    </div>
    @if($lowStockMedicines->isNotEmpty())
    <div class="bg-danger-50 dark:bg-danger-900/30 p-6 rounded-2xl border border-danger-200 dark:border-danger-800">
        <h3 class="mb-2 text-lg font-bold text-danger-800 dark:text-danger-400">Peringatan Stok Kritis!</h3>
        <p class="mb-4 text-sm text-danger-600 dark:text-danger-400">Berikut adalah daftar obat dengan sisa stok di bawah atau sama dengan batas minimum.</p>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-danger-200 dark:border-danger-800">
                    <th class="pb-4 px-4 font-semibold">Nama Obat</th>
                    <th class="pb-4 px-4 font-semibold">Sisa Stok</th>
                    <th class="pb-4 px-4 font-semibold">Batas Minimal</th>
                </tr>
            </x-slot>
            @foreach($lowStockMedicines as $item)
                <tr class="hover:bg-danger-100/50 dark:hover:bg-danger-900/20 transition-colors">
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-semibold">{{ $item->name }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $item->total_stock }} {{ $item->unit }}</td>
                    <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                </tr>
            @endforeach
        </x-table>
    </div>
    @endif

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
        <h3 class="mb-6 text-lg font-bold text-primary-800 dark:text-primary-400">Penerimaan Stok Masuk</h3>
        <form action="{{ route('medicine-stocks.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="medicine_id" :value="'Pilih Obat'" />
                    <select name="medicine_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                {{ $medicine->name }} (Stok: {{ $medicine->total_stock ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('medicine_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="batch_number" :value="'No. Batch'" />
                    <x-text-input type="text" name="batch_number" value="{{ old('batch_number') }}" placeholder="BATCH-XXXX" />
                </div>
                <div>
                    <x-input-label for="quantity" :value="'Jumlah (Qty)'" />
                    <x-text-input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required />
                    <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="expiry_date" :value="'Tanggal Kedaluwarsa'" />
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                    <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('medicines.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200 hover:bg-secondary-50 dark:hover:bg-secondary-900/30">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Proses Stok Masuk</button>
            </div>
        </form>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
        <h3 class="mb-6 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Stok Obat</h3>
        <x-table placeholder="Cari obat / batch..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Nama Obat</th>
                    <th class="pb-4 px-4 font-semibold">Total Stok</th>
                    <th class="pb-4 px-4 font-semibold">Minimal</th>
                    <th class="pb-4 px-4 font-semibold">Batch</th>
                    <th class="pb-4 px-4 font-semibold">Kedaluwarsa</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($medicines as $medicine)
                <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-semibold">{{ $medicine->name }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $medicine->total_stock ?? 0 }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $medicine->minimum_stock }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            @forelse($medicine->stocks as $stock)
                                <span class="mr-1 inline-flex items-center rounded-full bg-secondary-100 dark:bg-secondary-900/30 px-3 py-1 text-xs font-medium text-secondary-800 dark:text-secondary-400">{{ $stock->batch_number ?: '-' }} ({{ $stock->quantity }})</span>
                            @empty
                                <span class="text-text-secondary-light dark:text-text-secondary-dark">-</span>
                            @endforelse
                        </td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            @forelse($medicine->stocks as $stock)
                                @php
                                    $expiry = $stock->expiry_date;
                                    $days = $expiry ? now()->diffInDays($expiry, false) : null;
                                @endphp
                                @if($expiry && $days < 0)
                                    <span class="mr-1 inline-flex items-center rounded-full bg-danger-100 dark:bg-danger-900/30 px-2 py-0.5 text-xs font-semibold text-danger-800 dark:text-danger-400 border border-danger-200 dark:border-danger-800" title="Obat sudah kedaluwarsa">{{ $expiry->format('d/m/Y') }} (Kedaluwarsa)</span>
                                @elseif($expiry && $days <= 60)
                                    <span class="mr-1 inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2 py-0.5 text-xs font-semibold text-yellow-800 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800" title="Mendekati kedaluwarsa ({{ max(0, (int)$days) }} hari lagi)">{{ $expiry->format('d/m/Y') }} ({{ max(0, (int)$days) }} hr)</span>
                                @else
                                    <span class="mr-1 text-xs">{{ $expiry?->format('d/m/Y') }}</span>
                                @endif
                            @empty
                                <span class="text-text-secondary-light dark:text-text-secondary-dark">-</span>
                            @endforelse
                        </td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            <a href="{{ route('medicines.show', $medicine) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="6" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data obat.</td></tr>
                    @endforelse
            </x-table>
        <div class="mt-6">
            {{ $medicines->links() }}
        </div>
    </div>
</div>
@endsection