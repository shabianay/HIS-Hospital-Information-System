@extends('layouts.app')
@section('title', 'Edit Tarif Tindakan')
@section('content')
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Edit Tarif</h2>
        </div>
        <form action="{{ route('tariffs.update', $tariff) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="name" value="Nama Tindakan / Layanan" />
                    <x-text-input type="text" name="name" value="{{ old('name', $tariff->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="poli_id" value="Poli" />
                    <select name="poli_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Tidak Terikat Poli</option>
                        @foreach($polis as $poli)
                            <option value="{{ $poli->id }}" {{ (old('poli_id', $tariff->poli_id)) == $poli->id ? 'selected' : '' }}>{{ $poli->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="type" value="Jenis Tarif" />
                    <select name="type" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih Jenis</option>
                        @foreach(['konsultasi' => 'Konsultasi', 'tindakan' => 'Tindakan', 'penunjang' => 'Penunjang', 'lainnya' => 'Lainnya'] as $key => $label)
                            <option value="{{ $key }}" {{ old('type', $tariff->type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" />
                </div>
                <div>
                    <x-input-label for="price" value="Tarif / Harga (Rp)" />
                    <x-text-input type="number" name="price" value="{{ old('price', $tariff->price) }}" min="0" required />
                    <x-input-error :messages="$errors->get('price')" />
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $tariff->is_active ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Tarif aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('tariffs.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
