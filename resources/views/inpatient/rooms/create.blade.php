@extends('layouts.app')
@section('title', 'Tambah Kamar')
@section('content')
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Tambah Kamar Baru</h2>
        </div>
        <form action="{{ route('rooms.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="code" value="Kode Kamar" />
                        <x-text-input type="text" name="code" value="{{ old('code') }}" placeholder="contoh: VIP-01" required />
                        <x-input-error :messages="$errors->get('code')" />
                    </div>
                    <div>
                        <x-input-label for="name" value="Nama Kamar" />
                        <x-text-input type="text" name="name" value="{{ old('name') }}" placeholder="contoh: Kamar VIP 1" required />
                        <x-input-error :messages="$errors->get('name')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="room_type" value="Tipe Kamar" />
                        <select name="room_type" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            @foreach(\App\Models\Room::ROOM_TYPES as $key => $label)
                                <option value="{{ $key }}" {{ old('room_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('room_type')" />
                    </div>
                    <div>
                        <x-input-label for="price_per_day" value="Tarif per Hari (Rp)" />
                        <x-text-input type="number" name="price_per_day" value="{{ old('price_per_day') }}" min="0" step="0.01" required />
                        <x-input-error :messages="$errors->get('price_per_day')" />
                    </div>
                </div>
                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <textarea name="description" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" />
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Kamar aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('rooms.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection