@extends('layouts.app')

@section('title', 'Input Tanda Vital')

@section('content')
<div class="w-full">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Input Tanda Vital Pasien</h2>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                Janji Temu #{{ $appointment->queue_number }} · {{ $appointment->patient?->name }} · {{ $appointment->doctor?->name }}
            </p>
        </div>

        <form action="{{ route('vital-signs.store', $appointment) }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="temperature" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Suhu Tubuh (°C)</label>
                    <input type="number" step="0.1" name="temperature" id="temperature" value="{{ old('temperature') }}" required
                        class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    @error('temperature') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Tekanan Darah (mmHg)</label>
                    <div class="flex gap-2 mt-1">
                        <input type="number" name="blood_pressure_systolic" placeholder="Sistolik" value="{{ old('blood_pressure_systolic') }}" required
                            class="block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                        <span class="text-text-secondary-light dark:text-text-secondary-dark self-center">/</span>
                        <input type="number" name="blood_pressure_diastolic" placeholder="Diastolik" value="{{ old('blood_pressure_diastolic') }}" required
                            class="block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    </div>
                    @error('blood_pressure_systolic') <span class="text-danger-600">{{ $message }}</span> @enderror
                    @error('blood_pressure_diastolic') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="heart_rate" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Denyut Nadi (bpm)</label>
                    <input type="number" name="heart_rate" id="heart_rate" value="{{ old('heart_rate') }}" required
                        class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    @error('heart_rate') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="respiratory_rate" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Laju Pernapasan (x/menit)</label>
                    <input type="number" name="respiratory_rate" id="respiratory_rate" value="{{ old('respiratory_rate') }}" required
                        class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    @error('respiratory_rate') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="weight" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Berat Badan (kg)</label>
                    <input type="number" step="0.1" name="weight" id="weight" value="{{ old('weight') }}" required
                        class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    @error('weight') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="height" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" name="height" id="height" value="{{ old('height') }}" required
                        class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    @error('height') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="oxygen_saturation" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Saturasi Oksigen (%)</label>
                    <input type="number" name="oxygen_saturation" id="oxygen_saturation" value="{{ old('oxygen_saturation') }}" required
                        class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">
                    @error('oxygen_saturation') <span class="text-danger-600">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="notes" class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Catatan Tambahan</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full border border-border-light dark:border-border-dark rounded-xl shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark p-2.5">{{ old('notes') }}</textarea>
                @error('notes') <span class="text-danger-600">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-border-light dark:border-border-dark">
                <a href="{{ route('appointments.show', $appointment) }}" class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark text-text-primary-light dark:text-text-primary-dark text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">
                    Batal
                </a>
                <x-primary-button type="submit">
                    Simpan Tanda Vital
                </x-primary-button>
            </div>
        </form>
    </div>
</div>
@endsection
