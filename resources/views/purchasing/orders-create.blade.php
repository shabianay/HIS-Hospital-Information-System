@extends('layouts.app')
@section('title', 'Buat Purchase Order')
@section('content')
<div class="w-full space-y-6">
    <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Buat Purchase Order</h2>

    <form action="{{ route('purchasing.orders.store') }}" method="POST" x-data="poForm()" @submit="validateItems()">
        @csrf

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Informasi PO</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <x-input-label value="Supplier *" />
                    <select name="supplier_id" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Pilih supplier...</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label value="Tanggal PO *" />
                    <x-text-input type="date" name="order_date" value="{{ now()->toDateString() }}" required />
                </div>
                <div>
                    <x-input-label value="Perkiraan Tiba" />
                    <x-text-input type="date" name="expected_date" />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label value="Catatan (Opsional)" />
                <textarea name="notes" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
            </div>
        </div>

        <div class="mt-8 bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Item Barang</h3>
                <button type="button" @click="addRow()" class="inline-flex items-center justify-center px-4 py-2.5 border border-primary-300 dark:border-primary-700 text-primary-700 dark:text-primary-300 text-sm font-semibold rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-all">+ Tambah Baris</button>
            </div>

            @error('items')
                <div class="mb-4 rounded-xl bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 px-4 py-3 text-sm text-danger-700 dark:text-danger-400">{{ $message }}</div>
            @enderror

            <div class="space-y-3">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                        <div class="sm:col-span-5">
                            <x-input-label value="Obat" />
                            <select :name="'items[' + index + '][medicine_id]'" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                                <option value="">Pilih obat...</option>
                                @foreach($medicines as $medicine)
                                <option value="{{ $medicine->id }}" data-price="{{ $medicine->buy_price }}">{{ $medicine->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label value="Jumlah" />
                            <input type="number" x-model.number="row.quantity" :name="'items[' + index + '][quantity]'" min="1" placeholder="10" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        </div>
                        <div class="sm:col-span-3">
                            <x-input-label value="Harga Satuan" />
                            <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="row.unit_price" placeholder="1000" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        </div>
                        <div class="sm:col-span-1">
                            <button type="button" @click="rows.splice(index, 1)" class="p-2.5 text-danger-600 hover:text-red-700 dark:text-red-400" title="Hapus baris">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>
                </template>
                <p x-show="rows.length === 0" class="text-sm text-text-secondary-light dark:text-text-secondary-dark">Belum ada item. Klik "Tambah Baris" untuk menambahkan obat.</p>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Total: <span class="font-mono">Rp <span x-text="total().toLocaleString('id-ID')">0</span></span></p>
                <div class="flex gap-3">
                    <x-secondary-button href="{{ route('purchasing.orders') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan PO</x-primary-button>
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
function poForm() {
    return {
        rows: [],
        addRow() {
            this.rows.push({ medicine_id: '', quantity: 1, unit_price: 0 });
        },
        total() {
            return this.rows.reduce((sum, r) => sum + (r.quantity * r.unit_price), 0);
        },
        validateItems() {
            if (this.rows.length === 0) {
                event.preventDefault();
                alert('Minimal satu item obat harus diisi.');
            }
        }
    };
}
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[x-data]');
    if (form) {
        form.addEventListener('change', function (e) {
            if (e.target.tagName === 'SELECT' && e.target.matches('[name$="[medicine_id]"]')) {
                const opt = e.target.options[e.target.selectedIndex];
                const price = opt.dataset.price;
                const rowEl = e.target.closest('.grid');
                const priceInput = rowEl.querySelector('input[name$="[unit_price]"]');
                if (price && priceInput && !priceInput.value) {
                    priceInput.value = price;
                    const evt = new Event('input', { bubbles: true });
                    priceInput.dispatchEvent(evt);
                }
            }
        });
    }
});
</script>
@endpush
@endsection