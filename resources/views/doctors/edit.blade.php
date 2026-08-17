@extends('layouts.app')
@section('title', 'Edit Dokter')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark mb-8">Edit Dokter</h2>
        <form action="{{ route('doctors.update', $doctor) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="name" value="Nama Lengkap" />
                    <x-text-input type="text" name="name" value="{{ old('name', $doctor->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="specialization" value="Spesialisasi" />
                    <x-text-input type="text" name="specialization" value="{{ old('specialization', $doctor->specialization) }}" placeholder="Contoh: Penyakit Dalam" />
                    <x-input-error :messages="$errors->get('specialization')" />
                </div>
                <div>
                    <x-input-label for="license_number" value="No. SIP / STR" />
                    <x-text-input type="text" name="license_number" value="{{ old('license_number', $doctor->license_number) }}" required />
                    <x-input-error :messages="$errors->get('license_number')" />
                </div>
                <div>
                    <x-input-label for="user_id" value="Akun Login (Opsional)" />
                    <select name="user_id" id="user_id"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">— Tidak tertaut —</option>
                        @foreach($availableUsers as $user)
                            <option value="{{ $user->id }}" {{ (string) old('user_id', $doctor->user_id) === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Tautkan ke akun dengan role Dokter agar bisa masuk ke menu "Pasien Saya".</p>
                    <x-input-error :messages="$errors->get('user_id')" />
                </div>
                <div class="flex items-center mt-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $doctor->is_active ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
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