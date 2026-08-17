@extends('layouts.app')
@section('title', 'Detail Rekam Medis (EMR)')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Detail Rekam Medis (EMR)</h2>
            <div class="flex flex-wrap items-center gap-3">
                @if($medicalRecord->status === 'draft')
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">Draft</span>
                @else
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800">Final</span>
                @endif
                @if($medicalRecord->status === 'draft')
                    <x-secondary-button href="{{ route('medical-records.edit', $medicalRecord) }}">Edit</x-secondary-button>
                @endif
                <a href="{{ route('medical-records.pdf', $medicalRecord) }}" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-5 py-2.5 text-sm font-semibold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all duration-200">Cetak PDF</a>
                @if($medicalRecord->appointment && !$medicalRecord->appointment->billing)
                    <a href="{{ route('billings.create', $medicalRecord->appointment) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glass-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-surface-dark transition-all duration-200">Buat Tagihan</a>
                @endif
                @if($medicalRecord->appointment)
                    <a href="{{ route('lab.create', ['appointment_id' => $medicalRecord->appointment->id, 'medical_record_id' => $medicalRecord->id]) }}" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-5 py-2.5 text-sm font-semibold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all duration-200">Rujuk Lab</a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Pasien</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->patient?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. Antrian</span>
                <span class="block text-base font-medium font-mono text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->queue_number }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Kunjungan</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->appointment_date?->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter Pemeriksa</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->doctor?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Poli</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->appointment?->poli?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Keluhan Utama</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $medicalRecord->chief_complaint ?: '-' }}</span>
            </div>
        </div>

        <div class="mt-8 border-t border-border-light dark:border-border-dark pt-6">
            <h4 class="mb-3 text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Tanda-Tanda Vital</h4>
            <div class="grid grid-cols-2 gap-2 rounded-xl bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm md:grid-cols-6 border border-border-light dark:border-border-dark">
                <div class="text-text-primary-light dark:text-text-primary-dark"><strong>TDS:</strong> {{ $medicalRecord->blood_pressure_systolic ?? '-' }}</div>
                <div class="text-text-primary-light dark:text-text-primary-dark"><strong>TDD:</strong> {{ $medicalRecord->blood_pressure_diastolic ?? '-' }}</div>
                <div class="text-text-primary-light dark:text-text-primary-dark"><strong>HR:</strong> {{ $medicalRecord->heart_rate ?? '-' }} bpm</div>
                <div class="text-text-primary-light dark:text-text-primary-dark"><strong>Suhu:</strong> {{ $medicalRecord->temperature ?? '-' }} °C</div>
                <div class="text-text-primary-light dark:text-text-primary-dark"><strong>BB:</strong> {{ $medicalRecord->weight ?? '-' }} kg</div>
                <div class="text-text-primary-light dark:text-text-primary-dark"><strong>TB:</strong> {{ $medicalRecord->height ?? '-' }} cm</div>
            </div>
            @if($medicalRecord->allergy_notes)
                <div class="mt-3 rounded-xl border border-yellow-200 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20 p-3 text-sm text-yellow-800 dark:text-yellow-400">
                    <strong>Alergi:</strong> {{ $medicalRecord->allergy_notes }}
                </div>
            @endif
        </div>

        <div class="mt-8 space-y-4 border-t border-border-light dark:border-border-dark pt-6">
            <div>
                <h4 class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Subjective (S)</h4>
                <p class="mt-2 rounded-xl bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm text-text-primary-light dark:text-text-primary-dark border border-border-light dark:border-border-dark">{{ $medicalRecord->subjective ?: '-' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Objective (O)</h4>
                <p class="mt-2 rounded-xl bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm text-text-primary-light dark:text-text-primary-dark border border-border-light dark:border-border-dark">{{ $medicalRecord->objective ?: '-' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Assessment (A)</h4>
                <p class="mt-2 rounded-xl bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm text-text-primary-light dark:text-text-primary-dark border border-border-light dark:border-border-dark">{{ $medicalRecord->assessment ?: '-' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Plan (P)</h4>
                <p class="mt-2 rounded-xl bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm text-text-primary-light dark:text-text-primary-dark border border-border-light dark:border-border-dark">{{ $medicalRecord->plan ?: '-' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Diagnosis (ICD-10)</h4>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Kode ICD-10</th>
                    <th class="pb-3 font-semibold">Deskripsi</th>
                    <th class="pb-3 font-semibold">Primer</th>
                </tr>
            </x-slot>
            @forelse($medicalRecord->diagnoses as $diagnosis)
                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 font-mono text-text-primary-light dark:text-text-primary-dark">{{ $diagnosis->icd_code }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $diagnosis->description }}</td>
                        <td class="py-3">
                            @if($diagnosis->is_primary)
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-3 py-1 text-xs font-semibold text-primary-800 dark:text-primary-400">Ya</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-secondary-100 dark:bg-secondary-900/30 px-3 py-1 text-xs font-semibold text-secondary-700 dark:text-secondary-400">Tidak</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada diagnosis.</td></tr>
                    @endforelse
            </x-table>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Resep Obat</h4>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Obat</th>
                    <th class="pb-3 font-semibold">Jumlah</th>
                    <th class="pb-3 font-semibold">Dosis</th>
                    <th class="pb-3 font-semibold">Frekuensi</th>
                    <th class="pb-3 font-semibold">Durasi</th>
                    <th class="pb-3 font-semibold">Instruksi</th>
                    <th class="pb-3 font-semibold">Status</th>
                    <th class="pb-3 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($medicalRecord->prescriptions as $prescription)
                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->medicine?->name }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->quantity }} {{ $prescription->medicine?->unit }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->dosage }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->frequency }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->duration ?: '-' }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $prescription->instructions ?: '-' }}</td>
                        <td class="py-3">
                            @if($prescription->is_dispensed)
                                <span class="inline-flex items-center rounded-full bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 px-3 py-1 text-xs font-semibold border border-success-200 dark:border-success-800">Didispersi</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 px-3 py-1 text-xs font-semibold border border-yellow-200 dark:border-yellow-800">Belum</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if(!$prescription->is_dispensed)
                                <form action="{{ route('prescriptions.dispense', $prescription) }}" method="POST" onsubmit="return confirm('Dispensasi resep ini dan kurangi stok?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-1.5 text-xs font-semibold text-white shadow hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-200">Dispensasi</button>
                                </form>
                            @else
                                <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada resep.</td></tr>
                    @endforelse
            </x-table>
        </div>
        <div class="mt-6 flex justify-end">
            <x-secondary-button href="{{ route('medical-records.index') }}">Kembali</x-secondary-button>
        </div>
    </div>
</div>
@endsection
