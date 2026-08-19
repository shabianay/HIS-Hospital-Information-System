@extends('layouts.app')
@section('title', 'Buat Stock Opname')
@section('content')
<div class="w-full">
    <a href="{{ route('stock-opname.index') }}" class="inline-flex items-center text-sm text-primary-600 dark:text-primary-400 hover:underline mb-6">← Kembali ke Daftar Stock Opname</a>
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Buat Stock Opname</h2>

        <form method="POST" action="{{ route('stock-opname.store') }}" class="space-y-5"
            x-data="{
                items: [],
                medicines: @js($medicines->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'unit' => $m->unit,
                    'system_qty' => (int) $m->medicineStocks->sum('quantity'),
                ])->values()),
                get systemQty() { return 0; },
                addItem() {
                    const first = this.medicines[0];
                    this.items.push({ medicine_id: first?.id ?? '', actual_quantity: first?.system_qty ?? 0, notes: '' });
                },
                removeItem(i) { this.items.splice(i, 1); },
                medicineInfo(id) { return this.medicines.find(m => m.id == id); }
            }">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label value="Tanggal Opname *" />
                    <x-text-input type="date" name="opname_date" value="{{ now()->toDateString() }}" required />
                </div>
                <div>
                    <x-input-label value="Catatan (Opsional)" />
                    <x-text-input type="text" name="notes" placeholder="Catatan umum" />
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <x-input-label value="Item Obat *" />
                    <button type="button" @click="addItem()" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Item
                    </button>
                </div>

                <template x-for="(item, index) in items" :key="index">
                    <div class="border border-border-light dark:border-border-dark rounded-xl p-4 mb-3 space-y-3 bg-secondary-50/50 dark:bg-secondary-800/30">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <select x-model="item.medicine_id" name="items[index][medicine_id]" required class="w-full border border-border-light bg-surface-light px-4 py-2.5 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                                    <option value="">-- Pilih Obat --</option>
                                    <template x-for="m in medicines" :key="m.id">
                                        <option :value="m.id" x-text="m.name + ' (' + m.unit + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <button type="button" @click="removeItem(index)" class="p-2 rounded-lg text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-900/20">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <x-input-label value="Stok Sistem" />
                                <div class="px-4 py-2.5 text-sm text-text-secondary-light dark:text-text-secondary-dark bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl" x-text="(medicineInfo(item.medicine_id)?.system_qty ?? 0) + ' ' + (medicineInfo(item.medicine_id)?.unit ?? '')"></div>
                            </div>
                            <div>
                                <x-input-label value="Stok Aktual *" />
                                <input type="number" x-model="item.actual_quantity" :name="'items[' + index + '][actual_quantity]'" min="0" required class="w-full border border-border-light bg-surface-light px-4 py-2.5 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" />
                            </div>
                            <div>
                                <x-input-label value="Catatan" />
                                <input type="text" x-model="item.notes" :name="'items[' + index + '][notes]'" class="w-full border border-border-light bg-surface-light px-4 py-2.5 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" />
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="items.length === 0">
                    <div class="text-center py-8 border-2 border-dashed border-border-light dark:border-border-dark rounded-xl text-sm text-text-secondary-light dark:text-text-secondary-dark">
                        Belum ada item. Klik "Tambah Item" untuk mulai.
                    </div>
                </template>

                @error('items') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-4">
                <x-primary-button type="submit">Simpan Stock Opname</x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection