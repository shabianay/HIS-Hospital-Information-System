@extends('layouts.app')
@section('title', 'Edit Pasien')
@section('content')
<div class="mx-auto max-w-2xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark mb-8">Edit Pasien</h2>
        <form action="{{ route('patients.update', $patient) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="nik" value="NIK" />
                    <x-text-input type="text" name="nik" value="{{ old('nik', $patient->nik) }}" maxlength="16" required />
                    <x-input-error :messages="$errors->get('nik')" />
                </div>
                <div>
                    <x-input-label for="name" value="Nama Lengkap" />
                    <x-text-input type="text" name="name" value="{{ old('name', $patient->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="date_of_birth" value="Tanggal Lahir" />
                        <x-text-input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : '') }}" required />
                        <x-input-error :messages="$errors->get('date_of_birth')" />
                    </div>
                    <div>
                        <x-input-label for="gender" value="Jenis Kelamin" />
                        <select name="gender" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="L" {{ (old('gender', $patient->gender)) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ (old('gender', $patient->gender)) === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        <x-input-error :messages="$errors->get('gender')" />
                    </div>
                </div>
                <div>
                    <x-input-label for="address" value="Alamat" />
                    <textarea name="address" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">{{ old('address', $patient->address) }}</textarea>
                </div>
                <div>
                    <x-input-label for="phone_number" value="No. Telepon" />
                    <x-text-input type="text" name="phone_number" value="{{ old('phone_number', $patient->phone_number) }}" />
                    <x-input-error :messages="$errors->get('phone_number')" />
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('patients.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
