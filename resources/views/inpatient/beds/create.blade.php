@extends('layouts.app')
@section('title', 'Tambah Tempat Tidur')
@section('content')
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Tambah Tempat Tidur Baru</h2>
        </div>
        <form action="{{ route('beds.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="room_id" value="Kamar" />
                    <select name="room_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih Kamar</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id', request('room_id')) == $room->id ? 'selected' : '' }}>{{ $room->name }} ({{ $room->code }})</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('room_id')" />
                </div>
                <div>
                    <x-input-label for="bed_number" value="No. Tempat Tidur" />
                    <x-text-input type="text" name="bed_number" value="{{ old('bed_number') }}" placeholder="contoh: 01" required />
                    <x-input-error :messages="$errors->get('bed_number')" />
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Tempat tidur aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('beds.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection