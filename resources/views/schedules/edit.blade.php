@extends('layouts.app')
@section('title', 'Edit Jadwal')
@section('content')
@php
    $days = ['senin' => 'Senin', 'selasa' => 'Selasa', 'rabu' => 'Rabu', 'kamis' => 'Kamis', 'jumat' => 'Jumat', 'sabtu' => 'Sabtu', 'minggu' => 'Minggu'];
@endphp
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Edit Jadwal</h2>
        </div>
        <form action="{{ route('schedules.update', $schedule) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <x-input-label for="doctor_id" value="Dokter" />
                    <select name="doctor_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih Dokter</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ (old('doctor_id', $schedule->doctor_id)) == $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('doctor_id')" />
                </div>
                <div>
                    <x-input-label for="poli_id" value="Poli" />
                    <select name="poli_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih Poli</option>
                        @foreach($polis as $poli)
                            <option value="{{ $poli->id }}" {{ (old('poli_id', $schedule->poli_id)) == $poli->id ? 'selected' : '' }}>{{ $poli->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('poli_id')" />
                </div>
                <div>
                    <x-input-label for="day_of_week" value="Hari" />
                    <select name="day_of_week" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                        <option value="">Pilih Hari</option>
                        @foreach($days as $key => $label)
                            <option value="{{ $key }}" {{ (old('day_of_week', $schedule->day_of_week)) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('day_of_week')" />
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="start_time" value="Jam Mulai" />
                        <x-text-input type="time" name="start_time" value="{{ old('start_time', $schedule->start_time->format('H:i')) }}" required />
                        <x-input-error :messages="$errors->get('start_time')" />
                    </div>
                    <div>
                        <x-input-label for="end_time" value="Jam Selesai" />
                        <x-text-input type="time" name="end_time" value="{{ old('end_time', $schedule->end_time->format('H:i')) }}" required />
                        <x-input-error :messages="$errors->get('end_time')" />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="daily_quota" value="Kuota Harian" />
                        <x-text-input type="number" name="daily_quota" value="{{ old('daily_quota', $schedule->daily_quota) }}" min="1" max="200" required />
                        <x-input-error :messages="$errors->get('daily_quota')" />
                    </div>
                    <div>
                        <x-input-label for="consultation_fee" value="Biaya Konsultasi (Rp)" />
                        <x-text-input type="number" name="consultation_fee" value="{{ old('consultation_fee', $schedule->consultation_fee) }}" min="0" required />
                        <x-input-error :messages="$errors->get('consultation_fee')" />
                    </div>
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ $schedule->is_active ? 'checked' : '' }} class="h-5 w-5 rounded border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-secondary-700 dark:checked:bg-primary-500 dark:focus:ring-primary-400 dark:focus:ring-offset-background-dark">
                    <label for="is_active" class="ml-3 text-base font-medium text-text-primary-light dark:text-text-primary-dark">Jadwal aktif</label>
                </div>
                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('schedules.index') }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
