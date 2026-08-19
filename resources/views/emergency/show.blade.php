@extends('layouts.app')
@section('title', 'Kunjungan IGD ' . $emergencyVisit->visit_number)
@section('content')
<div class="space-y-6">
    @php
        $triageBadges = [
            'red' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
            'green' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
            'black' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
        ];
        $triageLabels = ['red' => 'Merah', 'yellow' => 'Kuning', 'green' => 'Hijau', 'black' => 'Hitam'];
        $statusBadges = [
            'waiting' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
            'in_triage' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
            'treatment' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
            'observation' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border border-info-200 dark:border-info-800',
            'admitted' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
            'discharged' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
            'referred' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border border-info-200 dark:border-info-800',
            'deceased' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
        ];
    @endphp

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Kunjungan IGD {{ $emergencyVisit->visit_number }}</h2>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusBadges[$emergencyVisit->status] ?? '' }}">{{ \App\Models\EmergencyVisit::STATUSES[$emergencyVisit->status] ?? $emergencyVisit->status }}</span>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $triageBadges[$emergencyVisit->triage_level] ?? '' }}">Triase {{ $triageLabels[$emergencyVisit->triage_level] ?? $emergencyVisit->triage_level }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('emergency.index') }}" class="inline-flex items-center rounded-xl border border-border-light dark:border-border-dark px-5 py-2.5 text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800/50 transition-all duration-200">← Kembali</a>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Informasi Pasien</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 text-sm">
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Pasien:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->patient?->name }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Tiba:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->arrived_at?->format('d/m/Y H:i') }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Dokter:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->doctor?->name ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Didaftarkan oleh:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->createdBy?->name ?? '-' }}</strong></div>
        </div>
        <div class="mt-4 rounded-xl bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800 px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Keluhan Utama:</strong> {{ $emergencyVisit->chief_complaint }}
        </div>
        @if($emergencyVisit->triage_notes)
        <div class="mt-3 rounded-xl bg-secondary-50 dark:bg-secondary-900/10 border border-border-light dark:border-border-dark px-4 py-3 text-sm text-text-primary-light dark:text-text-primary-dark">
            <strong>Catatan Triase:</strong> {{ $emergencyVisit->triage_notes }}
        </div>
        @endif
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Tanda Vital</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-4 text-sm">
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Suhu:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->temperature ? $emergencyVisit->temperature . ' °C' : '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">TD:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->blood_pressure_systolic ? $emergencyVisit->blood_pressure_systolic . '/' . $emergencyVisit->blood_pressure_diastolic . ' mmHg' : '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Nadi:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->heart_rate ? $emergencyVisit->heart_rate . ' /menit' : '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Napas:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->respiratory_rate ? $emergencyVisit->respiratory_rate . ' /menit' : '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">SpO2:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->oxygen_saturation ? $emergencyVisit->oxygen_saturation . ' %' : '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">GCS:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $emergencyVisit->gcs ?? '-' }}</strong></div>
            <div><span class="text-text-secondary-light dark:text-text-secondary-dark">Status:</span> <strong class="text-text-primary-light dark:text-text-primary-dark">{{ \App\Models\EmergencyVisit::STATUSES[$emergencyVisit->status] ?? '-' }}</strong></div>
        </div>
    </div>

    <form action="{{ route('emergency.update', $emergencyVisit) }}" method="POST" class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        @csrf
        @method('PUT')
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Perbarui Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <x-input-label value="Status *" />
                <select name="status" required class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    @foreach(\App\Models\EmergencyVisit::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ $emergencyVisit->status == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label value="Kategori Triase" />
                <select name="triage_level" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    @foreach(\App\Models\EmergencyVisit::TRIAGE_LEVELS as $val => $label)
                        <option value="{{ $val }}" {{ $emergencyVisit->triage_level == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label value="Dokter Penanggung Jawab" />
                <select name="doctor_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    <option value="">Belum ada</option>
                    @foreach(\App\Models\Doctor::orderBy('name')->get() as $doctor)
                        <option value="{{ $doctor->id }}" {{ $emergencyVisit->doctor_id === $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label value="Dirujuk ke (jika status dirujuk)" />
                <x-text-input type="text" name="referred_to" value="{{ $emergencyVisit->referred_to }}" placeholder="Contoh: RS Harapan Bunda" />
            </div>
            <div>
                <x-input-label value="Catatan Discharge / Rujukan" />
                <textarea name="discharge_notes" rows="3" placeholder="Keterangan..." class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">{{ $emergencyVisit->discharge_notes }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <x-primary-button type="submit">Simpan Perubahan</x-primary-button>
        </div>
    </form>
</div>
@endsection