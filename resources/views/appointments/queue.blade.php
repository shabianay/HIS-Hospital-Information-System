@extends('layouts.app')
@section('title', 'Antrian Hari Ini')
@section('content')
@php
    $statuses = [
        'waiting' => [
            'label' => 'Menunggu',
            'color' => 'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400',
        ],
        'in_progress' => [
            'label' => 'Sedang Diperiksa',
            'color' => 'bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400',
        ],
        'completed' => [
            'label' => 'Selesai',
            'color' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400',
        ],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Antrian Hari Ini</h2>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">
                {{ $today->translatedFormat('l, d F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            @can('viewAny', \App\Models\Appointment::class)
            <a href="{{ route('appointments.queue.csv', ['date' => $today->toDateString()]) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                Export CSV
            </a>
            <a href="{{ route('appointments.index', ['date' => $today->toDateString()]) }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-surface-light border border-border-light text-text-primary-light hover:bg-secondary-50 text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200 dark:bg-surface-dark dark:border-border-dark dark:text-text-primary-dark dark:hover:bg-secondary-800">
                Daftar Janji Temu
            </a>
            <a href="{{ route('appointments.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                Daftar Pasien Baru
            </a>
            @endcan
        </div>
    </div>

    @forelse($groups as $poliId => $appointments)
        @php $poli = $appointments->first()->poli; @endphp
        <div class="bg-surface-light dark:bg-surface-dark rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-border-light dark:border-border-dark bg-secondary-50/50 dark:bg-secondary-900/30 flex items-center justify-between">
                <h3 class="font-bold text-text-primary-light dark:text-text-primary-dark">{{ $poli?->name }}</h3>
                <span class="text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark">
                    {{ $appointments->where('status', 'waiting')->count() }} menunggu ·
                    {{ $appointments->where('status', 'in_progress')->count() }} diperiksa ·
                    {{ $appointments->where('status', 'completed')->count() }} selesai
                </span>
            </div>

            <div class="divide-y divide-border-light dark:divide-border-dark">
                @foreach($appointments as $appointment)
                    @php $st = $statuses[$appointment->status] ?? null; @endphp
                    <div class="px-6 py-4 flex flex-col md:flex-row md:items-center gap-4 {{ $appointment->status === 'in_progress' ? 'bg-primary-50/60 dark:bg-primary-900/10' : '' }}">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-secondary-100 dark:bg-secondary-800 font-mono font-bold text-sm text-text-primary-light dark:text-text-primary-dark">
                                {{ str($appointment->queue_number)->afterLast('-')->toString() }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-semibold text-text-primary-light dark:text-text-primary-dark truncate">{{ $appointment->patient?->name }}</p>
                                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">
                                    {{ $appointment->doctor?->name }}
                                    @if($appointment->notes)
                                        · <span class="italic">{{ $appointment->notes }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($st)
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['color'] }} shrink-0">{{ $st['label'] }}</span>
                        @endif

                        <div class="flex items-center gap-2 shrink-0">
                            @if($appointment->status === 'waiting')
                                <a href="{{ route('vital-signs.create', $appointment) }}" class="inline-flex items-center justify-center px-4 py-2 bg-warning-600 hover:bg-warning-700 text-white text-xs font-semibold rounded-lg shadow-glass-sm transition-all duration-200">
                                    Tanda Vital
                                </a>
                                @can('update', $appointment)
                                <form action="{{ route('appointments.status.update', $appointment) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="in_progress">
                                    <input type="hidden" name="back" value="queue">
                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-info-600 hover:bg-info-700 text-white text-xs font-semibold rounded-lg shadow-glass-sm transition-all duration-200">
                                        Panggil
                                    </button>
                                </form>
                                @endcan
                            @endif
                            @if($appointment->status === 'in_progress')
                                @can('update', $appointment)
                                <form action="{{ route('appointments.status.update', $appointment) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <input type="hidden" name="back" value="queue">
                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-success-600 hover:bg-success-700 text-white text-xs font-semibold rounded-lg shadow-glass-sm transition-all duration-200">
                                        Selesai
                                    </button>
                                </form>
                                @endcan
                            @endif
                            <a href="{{ route('appointments.ticket', $appointment) }}" target="_blank"
                                class="inline-flex items-center justify-center px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">
                                Cetak Tiket
                            </a>
                            <a href="{{ route('appointments.show', $appointment) }}"
                                class="inline-flex items-center justify-center px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">
                                Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-surface-light dark:bg-surface-dark p-10 rounded-2xl border border-border-light dark:border-border-dark text-center shadow-glass-sm">
            <p class="font-semibold text-text-primary-light dark:text-text-primary-dark">Belum ada antrian hari ini.</p>
            <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">Daftarkan pasien baru untuk memulai antrian.</p>
            @can('create', \App\Models\Appointment::class)
            <a href="{{ route('appointments.create') }}"
                class="mt-6 inline-flex items-center justify-center px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-glass-sm transition-all duration-200">
                Daftar Pasien
            </a>
            @endcan
        </div>
    @endforelse
</div>
@endsection