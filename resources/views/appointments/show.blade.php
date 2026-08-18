@extends('layouts.app')
@section('title', 'Detail Janji Temu')
@section('content')
@php
    $statusBadge = [
        'waiting' => ['label' => 'Menunggu', 'color' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border-warning-200 dark:border-warning-800'],
        'in_progress' => ['label' => 'Sedang Diperiksa', 'color' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border-info-200 dark:border-info-800'],
        'completed' => ['label' => 'Selesai', 'color' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border-success-200 dark:border-success-800'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border-danger-200 dark:border-danger-800'],
    ];
    $badge = $statusBadge[$appointment->status] ?? $statusBadge['waiting'];
@endphp
<div class="mx-auto max-w-4xl space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-8 border-b border-border-light dark:border-border-dark">
            <div>
                <span class="text-sm font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">No. Antrian</span>
                <h2 class="text-4xl font-extrabold text-text-primary-light dark:text-text-primary-dark mt-1">{{ $appointment->queue_number }}</h2>
            </div>
            <div class="flex items-center gap-3">
                @if($appointment->status !== 'cancelled')
                    <a href="{{ route('appointments.ticket', $appointment) }}?auto=1" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20 px-4 py-1.5 text-sm font-semibold text-primary-700 dark:text-primary-300 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-all duration-200">Cetak Tiket</a>
                @endif
                <span class="inline-flex items-center rounded-full px-4 py-1.5 text-sm font-semibold border {{ $badge['color'] }}">{{ $badge['label'] }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Nama Pasien</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->patient?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">NIK Pasien</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->patient?->nik }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Poli</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->poli?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Dokter</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->doctor?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Tanggal Kunjungan</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->appointment_date?->format('d/m/Y') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Jadwal</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">
                    {{ $appointment->schedule ? ucfirst($appointment->schedule->day_of_week) . ', ' . $appointment->schedule->start_time->format('H:i') . ' - ' . $appointment->schedule->end_time->format('H:i') : '-' }}
                </span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Biaya Konsultasi</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($appointment->consultation_fee, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-1">Catatan</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $appointment->notes ?: '-' }}</span>
            </div>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-end gap-3 pt-8 border-t border-border-light dark:border-border-dark">
            @if($appointment->status === 'waiting')
                <form action="{{ route('appointments.status.update', $appointment) }}" method="POST" onsubmit="return confirm('Mulai pemeriksaan untuk pasien ini?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-info-600 hover:bg-info-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                        Mulai Periksa
                    </button>
                </form>
            @endif

            @if($appointment->status === 'in_progress')
                <form action="{{ route('appointments.status.update', $appointment) }}" method="POST" onsubmit="return confirm('Tandai pemeriksaan selesai?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-success-600 hover:bg-success-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                        Selesai
                    </button>
                </form>
            @endif

            @if(in_array($appointment->status, ['waiting', 'in_progress']))
                <form action="{{ route('appointments.status.update', $appointment) }}" method="POST" onsubmit="return confirm('Batalkan janji temu ini?')">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-danger-600 hover:bg-danger-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                        Batal
                    </button>
                </form>
            @endif

            @if($appointment->medicalRecord)
                <a href="{{ route('medical-records.show', $appointment->medicalRecord) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                    Lihat Rekam Medis
                </a>
            @elseif(in_array($appointment->status, ['waiting', 'in_progress']))
                <a href="{{ route('medical-records.create', $appointment) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                    Mulai Pemeriksaan (Rekam Medis)
                </a>
            @endif

            @if($appointment->billing)
                <a href="{{ route('billings.show', $appointment->billing) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                    Lihat Tagihan
                </a>
            @elseif(in_array($appointment->status, ['in_progress', 'completed']))
                <a href="{{ route('billings.create', $appointment) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                    Buat Tagihan
                </a>
            @endif

            @if($appointment->status === 'cancelled' || $appointment->status === 'waiting')
                @if(!$appointment->medicalRecord && !$appointment->billing)
                    <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" onsubmit="return confirm('Hapus janji temu ini? Tindakan ini permanen.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center px-5 py-2.5 bg-danger-600 hover:bg-danger-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                            Hapus
                        </button>
                    </form>
                @endif
            @endif

            <a href="{{ route('appointments.queue') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                Lihat Antrian Hari Ini
            </a>
            <a href="{{ route('appointments.index') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                Kembali
            </a>
            </div>
        </div>

        @if($appointment->vitalSign)
            <div class="mt-8 pt-8 border-t border-border-light dark:border-border-dark">
                <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark mb-6">Tanda Vital Pasien</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Suhu Tubuh</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->temperature }} °C</p>
                    </div>
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Tekanan Darah</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->blood_pressure_systolic }}/{{ $appointment->vitalSign->blood_pressure_diastolic }} mmHg</p>
                    </div>
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Nadi</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->heart_rate }} bpm</p>
                    </div>
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Respirasi</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->respiratory_rate }} x/menit</p>
                    </div>
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Berat Badan</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->weight }} kg</p>
                    </div>
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Tinggi Badan</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->height }} cm</p>
                    </div>
                    <div class="p-4 rounded-xl bg-secondary-50 dark:bg-secondary-800/40 border border-border-light dark:border-border-dark">
                        <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">Saturasi Oksigen</p>
                        <p class="font-bold text-lg">{{ $appointment->vitalSign->oxygen_saturation }} %</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-text-secondary-light dark:text-text-secondary-dark">Dicatat oleh: {{ $appointment->vitalSign->recordedBy?->name ?? 'Sistem' }} pada {{ $appointment->vitalSign->created_at->format('d/m/Y H:i') }}</p>
                <div class="mt-4">
                    <a href="{{ route('vital-signs.create', $appointment) }}" class="inline-flex items-center px-4 py-2 border border-border-light dark:border-border-dark rounded-xl text-sm font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800">Edit Tanda Vital</a>
                </div>
            </div>
        @else
            @can('update', $appointment)
                <div class="mt-8 pt-8 border-t border-border-light dark:border-border-dark">
                    <a href="{{ route('vital-signs.create', $appointment) }}" class="inline-flex items-center px-5 py-2.5 bg-warning-600 hover:bg-warning-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Input Tanda Vital</a>
                </div>
            @endcan
        @endif
    </div>
@endsection
