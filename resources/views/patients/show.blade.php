@extends('layouts.app')
@section('title', 'Detail Pasien')
@section('content')
@php
    $statusBadge = [
        'waiting' => ['label' => 'Menunggu', 'color' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border-warning-200 dark:border-warning-800'],
        'in_progress' => ['label' => 'Sedang Diperiksa', 'color' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 border-info-200 dark:border-info-800'],
        'completed' => ['label' => 'Selesai', 'color' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border-success-200 dark:border-success-800'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border-danger-200 dark:border-danger-800'],
    ];
@endphp
<div class="space-y-8">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-border-light dark:border-border-dark">
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Profil Pasien</h2>
            <div class="flex gap-2">
                <a href="{{ route('patients.medical-history', $patient) }}" class="inline-flex items-center justify-center px-4 py-2 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-sm font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Riwayat Medis</a>
                <a href="{{ route('patients.medical-history.pdf', $patient) }}" class="inline-flex items-center justify-center px-4 py-2 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-sm font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Riwayat Medis PDF</a>
                <a href="{{ route('patients.card', $patient) }}" class="inline-flex items-center justify-center px-4 py-2 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-sm font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Kartu Berobat</a>
                <a href="{{ route('patients.edit', $patient) }}" class="inline-flex items-center justify-center px-4 py-2 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark text-sm font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-all">Edit</a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. RM</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->rm_number ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">NIK</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->nik }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Nama Lengkap</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Lahir</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d/m/Y') . ' <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">(' . \Carbon\Carbon::parse($patient->date_of_birth)->age . ' tahun)</span>' : '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Jenis Kelamin</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. Telepon</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->phone_number ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Alamat</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->address ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Asuransi / BPJS</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->insurance_provider ?: '-' }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">No. Asuransi / BPJS</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->insurance_number ?: '-' }}</span>
            </div>
        </div>

        @if($patient->allergies || $patient->chronic_conditions)
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-border-light dark:border-border-dark pt-8">
                @if($patient->allergies)
                    <div class="rounded-xl border border-danger-200 dark:border-danger-800 bg-danger-50 dark:bg-danger-900/10 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-danger-600 dark:text-danger-400">Riwayat Alergi</p>
                        <p class="mt-2 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->allergies }}</p>
                    </div>
                @endif
                @if($patient->chronic_conditions)
                    <div class="rounded-xl border border-warning-200 dark:border-warning-800 bg-warning-50 dark:bg-warning-900/10 p-5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-warning-600 dark:text-warning-400">Penyakit Kronis</p>
                        <p class="mt-2 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $patient->chronic_conditions }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <h3 class="text-xl font-semibold text-text-primary-light dark:text-text-primary-dark">Saldo Tagihan Belum Lunas</h3>
            @if($unpaidBills->isNotEmpty())
                <a href="{{ route('billings.show', $unpaidBills->first()) }}" class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">Lihat Tagihan</a>
            @endif
        </div>
        @if($unpaidBills->isNotEmpty())
            <div class="rounded-xl bg-danger-50 dark:bg-danger-900/10 border border-danger-200 dark:border-danger-800 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-danger-600 dark:text-danger-400">Total Piutang</p>
                <p class="mt-1 text-3xl font-bold text-danger-700 dark:text-danger-400">Rp {{ number_format($outstandingTotal, 0, ',', '.') }}</p>
            </div>
            <div class="mt-4 space-y-3">
                @foreach($unpaidBills as $billing)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl bg-secondary-50 dark:bg-secondary-900/30 border border-border-light dark:border-border-dark px-5 py-3">
                        <div>
                            <p class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark">Tagihan #{{ str_pad($billing->id, 4, '0', STR_PAD_LEFT) }} · {{ $billing->created_at?->format('d/m/Y') }}</p>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">
                                Status: {{ $billing->status === 'unpaid' ? 'Belum Dibayar' : 'Dibayar Sebagian' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-danger-600 dark:text-danger-400">Rp {{ number_format((float) $billing->total_amount - (float) $billing->paid_amount, 0, ',', '.') }}</p>
                            <a href="{{ route('billings.show', $billing) }}" class="text-xs font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400">Detail →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-xl bg-success-50 dark:bg-success-900/10 border border-success-200 dark:border-success-800 px-5 py-4 text-sm text-success-700 dark:text-success-400">
                Tidak ada tagihan yang belum lunas. Seluruh tagihan pasien telah dibayar.
            </div>
        @endif
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <x-table placeholder="Cari poli / dokter..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                    <th class="pb-4 px-4 font-semibold">Tanggal</th>
                    <th class="pb-4 px-4 font-semibold">No. Antrian</th>
                    <th class="pb-4 px-4 font-semibold">Poli</th>
                    <th class="pb-4 px-4 font-semibold">Dokter</th>
                    <th class="pb-4 px-4 font-semibold">Status</th>
                    <th class="pb-4 px-4 font-semibold">Aksi</th>
                </tr>
            </x-slot>
            @forelse($recentVisits as $visit)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td class="py-3 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $visit->appointment_date?->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 font-mono text-sm text-text-primary-light dark:text-text-primary-dark">{{ $visit->queue_number }}</td>
                        <td class="py-3 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $visit->poli?->name }}</td>
                        <td class="py-3 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $visit->doctor?->name }}</td>
                        <td class="py-3 px-4">
                            @php $badge = $statusBadge[$visit->status] ?? $statusBadge['waiting']; @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge['color'] }}">{{ $badge['label'] }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('appointments.show', $visit) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="6" class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada kunjungan.</td></tr>
                    @endforelse
            </x-table>
        </div>
    </div>
</div>
@endsection