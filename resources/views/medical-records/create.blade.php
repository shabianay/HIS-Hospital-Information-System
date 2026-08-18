@extends('layouts.app')
@section('title', 'Pencatatan Rekam Medis (EMR)')
@section('content')
<div class="w-full" x-data="{ diagnoses: [{ icd_code: '', description: '', is_primary: '1' }], prescriptions: [{ medicine_id: '', quantity: 1, dosage: '', frequency: '', duration: '', instructions: '' }] }">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Pencatatan EMR (SOAP)</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-5 text-sm md:grid-cols-2">
                <div>
                    <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Pasien</span>
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->patient?->name }} ({{ $appointment->patient?->nik }})</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. Antrian</span>
                    <span class="block text-base font-medium font-mono text-text-primary-light dark:text-text-primary-dark">{{ $appointment->queue_number }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter Pemeriksa</span>
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->doctor?->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Poli</span>
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->poli?->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Kunjungan</span>
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->appointment_date?->format('d/m/Y') }}</span>
                </div>
            </div>
            @if($appointment->patient?->allergies || $appointment->patient?->chronic_conditions)
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @if($appointment->patient?->allergies)
                        <div class="rounded-xl border border-danger-200 dark:border-danger-800 bg-danger-50 dark:bg-danger-900/10 px-4 py-3 text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-danger-600 dark:text-danger-400">Riwayat Alergi</span>
                            <p class="mt-1 font-medium text-danger-800 dark:text-danger-400">{{ $appointment->patient->allergies }}</p>
                        </div>
                    @endif
                    @if($appointment->patient?->chronic_conditions)
                        <div class="rounded-xl border border-warning-200 dark:border-warning-800 bg-warning-50 dark:bg-warning-900/10 px-4 py-3 text-sm">
                            <span class="text-xs font-semibold uppercase tracking-wider text-warning-600 dark:text-warning-400">Penyakit Kronis</span>
                            <p class="mt-1 font-medium text-warning-800 dark:text-warning-400">{{ $appointment->patient->chronic_conditions }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <form action="{{ route('medical-records.store', $appointment) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-6">
                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-primary-800 dark:text-primary-400">Tanda-Tanda Vital (Vital Signs)</h3>
                        @if($appointment->vitalSign)
                            <span class="inline-flex items-center rounded-full bg-success-100 dark:bg-success-900/30 px-3 py-1 text-xs font-semibold text-success-800 dark:text-success-400 border border-success-200 dark:border-success-800">Terisi dari perawat</span>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="blood_pressure_systolic" value="Tekanan Darah Sistolik (mmHg)" />
                            <x-text-input type="number" name="blood_pressure_systolic" value="{{ old('blood_pressure_systolic', $appointment->vitalSign?->blood_pressure_systolic) }}" min="0" max="300" />
                        </div>
                        <div>
                            <x-input-label for="blood_pressure_diastolic" value="Tekanan Darah Diastolik (mmHg)" />
                            <x-text-input type="number" name="blood_pressure_diastolic" value="{{ old('blood_pressure_diastolic', $appointment->vitalSign?->blood_pressure_diastolic) }}" min="0" max="300" />
                        </div>
                        <div>
                            <x-input-label for="heart_rate" value="Detak Jantung (bpm)" />
                            <x-text-input type="number" name="heart_rate" value="{{ old('heart_rate', $appointment->vitalSign?->heart_rate) }}" min="0" max="300" />
                        </div>
                        <div>
                            <x-input-label for="temperature" value="Suhu Tubuh (°C)" />
                            <x-text-input type="number" step="0.1" name="temperature" value="{{ old('temperature', $appointment->vitalSign?->temperature) }}" min="30" max="45" />
                        </div>
                        <div>
                            <x-input-label for="weight" value="Berat Badan (kg)" />
                            <x-text-input type="number" step="0.1" name="weight" value="{{ old('weight', $appointment->vitalSign?->weight) }}" min="0" max="500" />
                        </div>
                        <div>
                            <x-input-label for="height" value="Tinggi Badan (cm)" />
                            <x-text-input type="number" step="0.1" name="height" value="{{ old('height', $appointment->vitalSign?->height) }}" min="0" max="300" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 border-b border-border-light dark:border-border-dark pb-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="chief_complaint" value="Keluhan Utama (Chief Complaint)" />
                        <textarea name="chief_complaint" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">{{ old('chief_complaint') }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="allergy_notes" value="Catatan Alergi" />
                        <textarea name="allergy_notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" placeholder="Riwayat alergi obat/makanan">{{ old('allergy_notes') }}</textarea>
                    </div>
                </div>

                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <h3 class="mb-4 text-lg font-semibold text-primary-800 dark:text-primary-400">Dokumentasi SOAP</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-input-label for="subjective" value="S (Subjective)" />
                            <textarea name="subjective" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" placeholder="Keluhan dari pasien..." required>{{ old('subjective') }}</textarea>
                            <x-input-error :messages="$errors->get('subjective')" />
                        </div>
                        <div>
                            <x-input-label for="objective" value="O (Objective)" />
                            <textarea name="objective" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" placeholder="Hasil pemeriksaan klinis..." required>{{ old('objective') }}</textarea>
                            <x-input-error :messages="$errors->get('objective')" />
                        </div>
                        <div>
                            <x-input-label for="assessment" value="A (Assessment / Diagnosis)" />
                            <textarea name="assessment" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" placeholder="Kesimpulan diagnosis..." required>{{ old('assessment') }}</textarea>
                            <x-input-error :messages="$errors->get('assessment')" />
                        </div>
                        <div>
                            <x-input-label for="plan" value="P (Plan / Terapi)" />
                            <textarea name="plan" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" placeholder="Tindakan lanjutan..." required>{{ old('plan') }}</textarea>
                            <x-input-error :messages="$errors->get('plan')" />
                        </div>
                    </div>
                </div>

                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-primary-800 dark:text-primary-400">Diagnosis (ICD-10)</h3>
                        <button type="button" @click="diagnoses.push({ icd_code: '', description: '', is_primary: '0' })" class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">+ Tambah Diagnosis</button>
                    </div>
                    <p class="mb-4 text-xs text-text-secondary-light dark:text-text-secondary-dark">Kode dan deskripsi ICD-10. Satu diagnosis wajib ditandai sebagai diagnosis primer (Ya).</p>
                    <div class="space-y-4">
                        <template x-for="(diag, index) in diagnoses" :key="index">
                            <div class="grid grid-cols-1 gap-3 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-4 md:grid-cols-12">
                                <div class="md:col-span-2">
                                    <x-input-label for="diagnoses[][icd_code]" value="Kode ICD-10" />
                                    <input type="text" name="diagnoses[][icd_code]" x-model="diag.icd_code" placeholder="Misal: I10" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>
                                </div>
                                <div class="md:col-span-7">
                                    <x-input-label for="diagnoses[][description]" value="Deskripsi" />
                                    <input type="text" name="diagnoses[][description]" x-model="diag.description" placeholder="Deskripsi diagnosis" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="diagnoses[][is_primary]" value="Diagnosis Primer?" />
                                    <select name="diagnoses[][is_primary]" x-model="diag.is_primary" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                </div>
                                <div class="flex items-end justify-end md:col-span-1">
                                    <button type="button" @click="diagnoses.splice(index, 1)" class="text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-primary-800 dark:text-primary-400">Resep Obat</h3>
                        <button type="button" @click="prescriptions.push({ medicine_id: '', quantity: 1, dosage: '', frequency: '', duration: '', instructions: '' })" class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">+ Tambah Baris Obat</button>
                    </div>
                    <div class="space-y-4">
                        <template x-for="(med, index) in prescriptions" :key="index">
                            <div class="grid grid-cols-2 gap-3 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-4 md:grid-cols-12">
                                <div class="col-span-2 md:col-span-3">
                                    <x-input-label for="prescriptions[][medicine_id]" value="Obat" />
                                    <select name="prescriptions[][medicine_id]" x-model="med.medicine_id" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                                        <option value="">Pilih Obat</option>
                                        @foreach($medicines as $medicine)
                                            <option value="{{ $medicine->id }}">{{ $medicine->name }} ({{ $medicine->unit }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <x-input-label for="prescriptions[][quantity]" value="Jumlah" />
                                    <input type="number" name="prescriptions[][quantity]" x-model="med.quantity" min="1" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="prescriptions[][dosage]" value="Dosis" />
                                    <input type="text" name="prescriptions[][dosage]" x-model="med.dosage" placeholder="Misal: 1 tablet" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="prescriptions[][frequency]" value="Frekuensi" />
                                    <input type="text" name="prescriptions[][frequency]" x-model="med.frequency" placeholder="Misal: 3x sehari" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>
                                </div>
                                <div class="md:col-span-1">
                                    <x-input-label for="prescriptions[][duration]" value="Durasi" />
                                    <input type="text" name="prescriptions[][duration]" x-model="med.duration" placeholder="Misal: 5 hari" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">
                                </div>
                                <div class="col-span-2 md:col-span-2">
                                    <x-input-label for="prescriptions[][instructions]" value="Instruksi" />
                                    <input type="text" name="prescriptions[][instructions]" x-model="med.instructions" placeholder="Setelah makan" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">
                                </div>
                                <div class="flex items-end justify-end md:col-span-1">
                                    <button type="button" @click="prescriptions.splice(index, 1)" class="text-danger-600 hover:text-danger-800 dark:text-danger-400 dark:hover:text-danger-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('appointments.show', $appointment) }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan Rekam Medis</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
