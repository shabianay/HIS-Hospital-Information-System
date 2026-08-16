@extends('layouts.app')
@section('title', 'Edit Obat')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Edit Obat</h2>
        </div>
        <form action="{{ route('medicines.update', $medicine) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="name" value="Nama Obat" />
                    <x-text-input type="text" name="name" value="{{ old('name', $medicine->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="generic_name" value="Nama Generik" />
                    <x-text-input type="text" name="generic_name" value="{{ old('generic_name', $medicine->generic_name) }}" />
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="category" value="Kategori" />
                        <x-text-input type="text" name="category" value="{{ old('category', $medicine->category) }}" />
                    </div>
                    <div>
                        <x-input-label for="unit" value="Satuan / Unit" />
                        <select name="unit" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Satuan</option>
                            @foreach(['Tablet', 'Kapsul', 'Botol', 'Sirup', 'Tube', 'Ampul', 'Strip'] as $unit)
                                <option value="{{ $unit }}" {{ old('unit', $medicine->unit) === $unit ? 'selected' : '' }}>{{ $unit }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('unit')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="buy_price" value="Harga Beli (Rp)" />
                        <x-text-input type="number" name="buy_price" value="{{ old('buy_price', $medicine->buy_price) }}" min="0" required />
                        <x-input-error :messages="$errors->get('buy_price')" />
                    </div>
                    <div>
                        <x-input-label for="sell_price" value="Harga Jual (Rp)" />
                        <x-text-input type="number" name="sell_price" value="{{ old('sell_price', $medicine->sell_price) }}" min="0" required />
                        <x-input-error :messages="$errors->get('sell_price')" />
                    </div>
                </div>
                <div>
                    <x-input-label for="minimum_stock" value="Stok Minimum" />
                    <x-text-input type="number" name="minimum_stock" value="{{ old('minimum_stock', $medicine->minimum_stock) }}" min="0" required />
                    <x-input-error :messages="$errors->get('minimum_stock')" />
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $medicine->is_active ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Obat aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('medicines.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
