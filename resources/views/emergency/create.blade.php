@extends('layouts.app')
@section('title', 'Daftar Pasien IGD')
@section('content')
<div class="w-full space-y-6">
    <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Pasien IGD</h2>

    <form action="{{ route('emergency.store') }}" method="POST" class="grid grid-cols-1 gap-6">
        @csrf

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Pasien</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <x-input-label value="Pasien *" />
                    <select name="patient_id" required
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Pilih pasien...</option>
                        @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" {{ $preselectedPatient && $preselectedPatient->id === $patient->id ? 'selected' : '' }}>{{ $patient->name }} ({{ $patient->patient_number }})</option>
                        @endforeach
                    </select>
                    @error('patient_id')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label value="Dokter Penanggung Jawab" />
                    <select name="doctor_id"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Belum ada</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <p class="mt-4 text-sm text-text-secondary-light dark:text-text-secondary-dark">Pasien belum terdaftar? <a href="{{ route('patients.create') }}" class="text-primary-600 dark:text-primary-400 font-semibold">Buat pasien baru</a> terlebih dahulu.</p>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Triase</h3>
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <x-input-label value="Keluhan Utama *" />
                    <x-text-input type="text" name="chief_complaint" required placeholder="Contoh: Nyeri dada, sesak napas" />
                </div>
                <div>
                    <x-input-label value="Kategori Triase *" />
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach(['red' => ['Merah', 'Resusitasi - segera'], 'yellow' => ['Kuning', 'Urgent - ditangani cepat'], 'green' => ['Hijau', 'Non-Urgent'], 'black' => ['Hitam', 'Meninggal']] as $val => $info)
                        <label class="flex items-start gap-3 rounded-xl border border-border-light dark:border-border-dark px-4 py-3 hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors cursor-pointer">
                            <input type="radio" name="triage_level" value="{{ $val }}" required class="mt-1 h-4 w-4 border-border-light text-primary-600 focus:ring-primary-500 dark:border-border-dark dark:bg-surface-dark dark:ring-offset-background-dark">
                            <span>
                                <span class="block text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">{{ $info[0] }}</span>
                                <span class="block text-xs text-text-secondary-light dark:text-text-secondary-dark">{{ $info[1] }}</span>
                            </span>
                        </label>
                        @endforeach
                    </div>
                    @error('triage_level')<p class="mt-1 text-xs text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-input-label value="Catatan Triase (Opsional)" />
                    <textarea name="triage_notes" rows="2" placeholder="Keterangan tambahan..." class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Tanda Vital (Opsional)</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <x-input-label value="Suhu (°C)" />
                    <x-text-input type="number" name="temperature" min="30" max="45" step="0.1" placeholder="36.5" />
                </div>
                <div>
                    <x-input-label value="TD Sistolik" />
                    <x-text-input type="number" name="blood_pressure_systolic" min="40" max="300" placeholder="120" />
                </div>
                <div>
                    <x-input-label value="TD Diastolik" />
                    <x-text-input type="number" name="blood_pressure_diastolic" min="20" max="200" placeholder="80" />
                </div>
                <div>
                    <x-input-label value="Nadi (/menit)" />
                    <x-text-input type="number" name="heart_rate" min="20" max="250" placeholder="80" />
                </div>
                <div>
                    <x-input-label value="Napas (/menit)" />
                    <x-text-input type="number" name="respiratory_rate" min="4" max="80" placeholder="16" />
                </div>
                <div>
                    <x-input-label value="SpO2 (%)" />
                    <x-text-input type="number" name="oxygen_saturation" min="50" max="100" placeholder="98" />
                </div>
            </div>
            <div class="mt-4">
                <x-input-label value="GCS (3-15)" />
                <x-text-input type="number" name="gcs" min="3" max="15" placeholder="15" />
            </div>
        </div>

        <div class="flex items-center justify-between">
            <x-secondary-button href="{{ route('emergency.index') }}">Batal</x-secondary-button>
            <x-primary-button type="submit">Daftarkan Pasien IGD</x-primary-button>
        </div>
    </form>
</div>
@endsection