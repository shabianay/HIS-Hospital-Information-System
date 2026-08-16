@extends('layouts.app')
@section('title', 'Tambah Dokter')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark mb-8">Tambah Dokter Baru</h2>
        <form action="{{ route('doctors.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="name" value="Nama Lengkap" />
                    <x-text-input type="text" name="name" value="{{ old('name') }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="specialization" value="Spesialisasi" />
                    <x-text-input type="text" name="specialization" value="{{ old('specialization') }}" placeholder="Contoh: Penyakit Dalam" />
                    <x-input-error :messages="$errors->get('specialization')" />
                </div>
                <div>
                    <x-input-label for="license_number" value="No. SIP / STR" />
                    <x-text-input type="text" name="license_number" value="{{ old('license_number') }}" required />
                    <x-input-error :messages="$errors->get('license_number')" />
                </div>
                <div class="flex items-center mt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Dokter aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('doctors.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection