@extends('layouts.app')
@section('title', 'Edit Poli')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark mb-8">Edit Poli</h2>
        <form action="{{ route('polis.update', $poli) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="name" value="Nama Poli" />
                    <x-text-input type="text" name="name" value="{{ old('name', $poli->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="code" value="Kode Poli" />
                    <x-text-input type="text" name="code" value="{{ old('code', $poli->code) }}" maxlength="10" required />
                    <x-input-error :messages="$errors->get('code')" />
                </div>
                <div>
                    <x-input-label for="description" value="Deskripsi" />
                    <textarea name="description" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">{{ old('description', $poli->description) }}</textarea>
                </div>
                <div class="flex items-center mt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $poli->is_active ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Poli aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('polis.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
