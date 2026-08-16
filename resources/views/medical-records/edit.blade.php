@extends('layouts.app')
@section('title', 'Edit Rekam Medis (EMR)')
@section('content')
<div class="mx-auto max-w-4xl">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Edit Rekam Medis (EMR)</h2>
            @if($medicalRecord->status === 'draft')
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">Draft</span>
            @else
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Final</span>
            @endif
        </div>
        <div class="mb-8 grid grid-cols-1 gap-4 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-5 text-sm md:grid-cols-2">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Pasien</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->patient?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. Antrian</span>
                <span class="block text-base font-medium font-mono text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->queue_number }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter Pemeriksa</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->doctor?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Kunjungan</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->appointment_date?->format('d/m/Y') }}</span>
            </div>
        </div>

        <form action="{{ route('medical-records.update', $medicalRecord) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 gap-6">
                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <h3 class="mb-4 text-lg font-semibold text-primary-800 dark:text-primary-400">Tanda-Tanda Vital (Vital Signs)</h3>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        <div>
                            <x-input-label for="blood_pressure_systolic" value="Tekanan Darah Sistolik (mmHg)" />
                            <x-text-input type="number" name="blood_pressure_systolic" value="{{ old('blood_pressure_systolic', $medicalRecord->blood_pressure_systolic) }}" min="0" max="300" />
                        </div>
                        <div>
                            <x-input-label for="blood_pressure_diastolic" value="Tekanan Darah Diastolik (mmHg)" />
                            <x-text-input type="number" name="blood_pressure_diastolic" value="{{ old('blood_pressure_diastolic', $medicalRecord->blood_pressure_diastolic) }}" min="0" max="300" />
                        </div>
                        <div>
                            <x-input-label for="heart_rate" value="Detak Jantung (bpm)" />
                            <x-text-input type="number" name="heart_rate" value="{{ old('heart_rate', $medicalRecord->heart_rate) }}" min="0" max="300" />
                        </div>
                        <div>
                            <x-input-label for="temperature" value="Suhu Tubuh (°C)" />
                            <x-text-input type="number" step="0.1" name="temperature" value="{{ old('temperature', $medicalRecord->temperature) }}" min="30" max="45" />
                        </div>
                        <div>
                            <x-input-label for="weight" value="Berat Badan (kg)" />
                            <x-text-input type="number" step="0.1" name="weight" value="{{ old('weight', $medicalRecord->weight) }}" min="0" max="500" />
                        </div>
                        <div>
                            <x-input-label for="height" value="Tinggi Badan (cm)" />
                            <x-text-input type="number" step="0.1" name="height" value="{{ old('height', $medicalRecord->height) }}" min="0" max="300" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 border-b border-border-light dark:border-border-dark pb-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="chief_complaint" value="Keluhan Utama (Chief Complaint)" />
                        <textarea name="chief_complaint" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">{{ old('chief_complaint', $medicalRecord->chief_complaint) }}</textarea>
                    </div>
                    <div>
                        <x-input-label for="allergy_notes" value="Catatan Alergi" />
                        <textarea name="allergy_notes" rows="3" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">{{ old('allergy_notes', $medicalRecord->allergy_notes) }}</textarea>
                    </div>
                </div>

                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <h3 class="mb-4 text-lg font-semibold text-primary-800 dark:text-primary-400">Dokumentasi SOAP</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <x-input-label for="subjective" value="S (Subjective)" />
                            <textarea name="subjective" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>{{ old('subjective', $medicalRecord->subjective) }}</textarea>
                            <x-input-error :messages="$errors->get('subjective')" />
                        </div>
                        <div>
                            <x-input-label for="objective" value="O (Objective)" />
                            <textarea name="objective" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>{{ old('objective', $medicalRecord->objective) }}</textarea>
                            <x-input-error :messages="$errors->get('objective')" />
                        </div>
                        <div>
                            <x-input-label for="assessment" value="A (Assessment / Diagnosis)" />
                            <textarea name="assessment" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>{{ old('assessment', $medicalRecord->assessment) }}</textarea>
                            <x-input-error :messages="$errors->get('assessment')" />
                        </div>
                        <div>
                            <x-input-label for="plan" value="P (Plan / Terapi)" />
                            <textarea name="plan" rows="2" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark" required>{{ old('plan', $medicalRecord->plan) }}</textarea>
                            <x-input-error :messages="$errors->get('plan')" />
                        </div>
                    </div>
                </div>

                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <h3 class="mb-4 text-lg font-semibold text-primary-800 dark:text-primary-400">Status Rekam Medis</h3>
                    <select name="status" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="draft" {{ old('status', $medicalRecord->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="finalized" {{ old('status', $medicalRecord->status) === 'finalized' ? 'selected' : '' }}>Final (Terkunci)</option>
                    </select>
                    <p class="mt-2 text-xs text-text-secondary-light dark:text-text-secondary-dark">Catatan: setelah berstatus final, rekam medis tidak dapat diedit lagi.</p>
                </div>

                @if($medicalRecord->diagnoses->isNotEmpty() || $medicalRecord->prescriptions->isNotEmpty())
                <div class="border-b border-border-light dark:border-border-dark pb-6">
                    <h3 class="mb-3 text-lg font-semibold text-primary-800 dark:text-primary-400">Diagnosis & Resep Tersimpan</h3>
                    @if($medicalRecord->diagnoses->isNotEmpty())
                        <p class="mb-1 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Diagnosis (ICD-10)</p>
                        <div class="mb-4 flex flex-wrap gap-2">
                            @foreach($medicalRecord->diagnoses as $diagnosis)
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-semibold text-primary-800 dark:text-primary-400 border border-primary-200 dark:border-primary-800">
                                    {{ $diagnosis->icd_code }} — {{ $diagnosis->description }} {!! $diagnosis->is_primary ? '(<span class="text-primary-600 dark:text-primary-400">Primer</span>)' : '' !!}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if($medicalRecord->prescriptions->isNotEmpty())
                        <p class="mb-1 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">Resep Obat</p>
                        <x-table :searchable="false">
                            <x-slot name="head">
                                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                                    <th class="pb-2 font-semibold">Obat</th>
                                    <th class="pb-2 font-semibold">Jumlah</th>
                                    <th class="pb-2 font-semibold">Dosis</th>
                                    <th class="pb-2 font-semibold">Frekuensi</th>
                                    <th class="pb-2 font-semibold">Durasi</th>
                                </tr>
                            </x-slot>
                            @foreach($medicalRecord->prescriptions as $prescription)
                                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->medicine?->name }}</td>
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->quantity }} {{ $prescription->medicine?->unit }}</td>
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->dosage }}</td>
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->frequency }}</td>
                                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->duration ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </x-table>
                    @endif
                </div>
                @endif

                <div class="flex justify-end gap-3 pt-6">
                    <x-secondary-button href="{{ route('medical-records.show', $medicalRecord) }}">Batal</x-secondary-button>
                    <x-primary-button type="submit">Simpan Perubahan</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
