@extends('layouts.app')
@section('title', 'PO ' . $purchaseOrder->po_number)
@section('content')
<div class="space-y-6">
    @php
        $badges = [
            'draft' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800',
            'ordered' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border border-info-200 dark:border-info-800',
            'received' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
            'cancelled' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
        ];
    @endphp

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Purchase Order {{ $purchaseOrder->po_number }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badges[$purchaseOrder->status] ?? '' }}">{{ \App\Models\PurchaseOrder::STATUSES[$purchaseOrder->status] ?? $purchaseOrder->status }}</span>
        </div>
        <a href="{{ route('purchasing.orders') }}" class="inline-flex items-center rounded-xl border border-border-light dark:border-border-dark px-5 py-2.5 text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-all duration-200">← Kembali</a>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Informasi PO</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-sm">
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Supplier:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $purchaseOrder->supplier?->name ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Tanggal PO:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $purchaseOrder->order_date?->format('d/m/Y') }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Perkiraan Tiba:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $purchaseOrder->expected_date?->format('d/m/Y') ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Dibuat oleh:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $purchaseOrder->createdBy?->name ?? '-' }}</strong></div>
        </div>
        @if($purchaseOrder->notes)
        <div class="mt-4 rounded-xl bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800 px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Catatan:</strong> {{ $purchaseOrder->notes }}
        </div>
        @endif
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Item Barang</h3>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-4 px-4 font-semibold">Obat</th>
                    <th class="pb-4 px-4 font-semibold">Jumlah</th>
                    <th class="pb-4 px-4 font-semibold">Harga Satuan</th>
                    <th class="pb-4 px-4 font-semibold">Subtotal</th>
                </tr>
            </x-slot>
            @forelse($purchaseOrder->items as $item)
            <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-4 px-4 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $item->medicine?->name ?? $item->medicine_id }}</td>
                <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $item->quantity }}</td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="py-4 px-4 text-sm font-mono text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada item.</td></tr>
            @endforelse
        </x-table>
        <div class="mt-6 flex justify-end">
            <p class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Total: <span class="font-mono text-primary-600 dark:text-primary-400">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span></p>
        </div>
    </div>

    @if(in_array($purchaseOrder->status, ['draft', 'ordered']))
    <form action="{{ route('purchasing.orders.status', $purchaseOrder) }}" method="POST" class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        @csrf
        @method('PATCH')
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Perbarui Status</h3>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <x-input-label value="Status:" />
                <select name="status" class="border border-border-light bg-surface-light px-3 py-2 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-lg dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    @foreach(['ordered' => 'Dipesan', 'received' => 'Diterima (stok masuk)', 'cancelled' => 'Dibatalkan'] as $val => $label)
                        <option value="{{ $val }}" {{ $purchaseOrder->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button type="submit">Simpan Status</x-primary-button>
        </div>
    </form>
    @endif

    @if($purchaseOrder->status !== 'received')
    <div class="flex justify-end">
        <form method="POST" action="{{ route('purchasing.orders.destroy', $purchaseOrder) }}" onsubmit="return confirm('Hapus PO ini?')" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-5 py-2.5 border border-danger-200 dark:border-danger-800 text-danger-700 dark:text-danger-400 text-sm font-semibold rounded-xl hover:bg-danger-50 dark:hover:bg-danger-900/20 transition-all duration-200">Hapus PO</button>
        </form>
    </div>
    @endif
</div>
@endsection