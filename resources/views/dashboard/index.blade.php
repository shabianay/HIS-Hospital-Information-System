@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-semibold tracking-tight text-text-primary-light dark:text-text-primary-dark">
            Selamat datang, {{ Auth::user()->name }} 👋</h2>
        <p class="mt-1 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ now()->format('l, d F Y') }}</p>
    </div>

    @if ($canManagePatients || $canManageAppointments || $canManageBilling || $canManagePharmacy)
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @can('manage-patients')
                <a href="{{ route('patients.index') }}"
                    class="group flex items-center gap-4 bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-600 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Total Pasien Hari Ini</div>
                        <div class="mt-1 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ number_format($totalPatientsToday) }}</div>
                    </div>
                </a>
            @endcan
            @can('manage-appointments')
                <a href="{{ route('appointments.queue') }}"
                    class="group flex items-center gap-4 bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent-100 text-accent-600 dark:bg-accent-900/40 dark:text-accent-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Total Kunjungan Hari Ini</div>
                        <div class="mt-1 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">{{ number_format($appointmentsToday) }}</div>
                    </div>
                </a>
            @endcan
            @can('manage-billing')
                <a href="{{ route('billings.daily-report') }}"
                    class="group flex items-center gap-4 bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-success-100 text-success-600 dark:bg-success-900/40 dark:text-success-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Pendapatan Hari Ini</div>
                        <div class="mt-1 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($revenueToday, 0, ',', '.') }}</div>
                    </div>
                </a>
            @endcan
            @can('manage-pharmacy')
                <a href="{{ route('medicines.stock') }}"
                    class="group flex items-center gap-4 bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-danger-100 text-danger-600 dark:bg-danger-900/40 dark:text-danger-300">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Stok Obat Menipis</div>
                        <div class="mt-1 text-3xl font-bold text-danger-600 dark:text-danger-400">{{ number_format($lowStockCount) }}</div>
                    </div>
                </a>
            @endcan
        </div>
    @endif

    @if ($canManageAppointments || $canManageInpatient || $canManageLab || $canManagePharmacy || $canManageBilling)
        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-5">
            @can('manage-appointments')
                <a href="{{ route('appointments.queue') }}"
                    class="group bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Antrian Menunggu</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 text-lg font-bold">{{ $waitingQueueToday }}</span>
                    </div>
                    <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Hari ini</div>
                </a>
            @endcan
            @can('manage-inpatient')
                <a href="{{ route('admissions.index') }}"
                    class="group bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Pasien Rawat Inap</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 text-lg font-bold">{{ $activeInpatientsCount }}</span>
                    </div>
                    <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Sedang dirawat</div>
                </a>
            @endcan
            @can('manage-lab')
                <a href="{{ route('lab.requests') }}"
                    class="group bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Permintaan Lab Pending</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 text-lg font-bold">{{ $pendingLabCount }}</span>
                    </div>
                    <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Belum selesai</div>
                </a>
            @endcan
            @can('manage-pharmacy')
                <a href="{{ route('prescriptions.pending') }}"
                    class="group bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Resep Belum Diserahkan</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 text-lg font-bold">{{ $pendingPrescriptionsCount }}</span>
                    </div>
                    <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Menunggu dispense</div>
                </a>
            @endcan
            @can('manage-billing')
                <a href="{{ route('billings.index') }}"
                    class="group bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Tagihan Belum Lunas</div>
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 text-lg font-bold">{{ $unpaidBillsCount }}</span>
                    </div>
                    <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Belum / sebagian</div>
                </a>
            @endcan
        </div>
    @endif

    @can('manage-appointments')
        <div class="mt-6 grid grid-cols-1 gap-6">
            <div
                class="bg-surface-light dark:bg-surface-dark p-6 sm:p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Janji Temu Terkini</h3>
                    <a href="{{ route('appointments.queue') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">Lihat semua</a>
                </div>
                <x-table :searchable="false">
                    <x-slot name="head">
                        <tr
                            class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                            <th class="pb-3 font-semibold">No Antrean</th>
                            <th class="pb-3 font-semibold">Pasien</th>
                            <th class="pb-3 font-semibold">Dokter</th>
                            <th class="pb-3 font-semibold">Status</th>
                        </tr>
                    </x-slot>
                    @forelse($recentAppointments as $appt)
                        <tr
                            class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                            <td class="py-3"><a href="{{ route('appointments.show', $appt->id) }}" class="text-text-primary-light dark:text-text-primary-dark hover:text-primary-600 dark:hover:text-primary-400">{{ $appt->queue_number }}</a></td>
                            <td class="py-3"><a href="{{ route('appointments.show', $appt->id) }}" class="text-text-primary-light dark:text-text-primary-dark hover:text-primary-600 dark:hover:text-primary-400">{{ $appt->patient_name }}</a></td>
                            <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $appt->doctor_name }}</td>
                            <td class="py-3">
                                @php
                                    $badge = [
                                        'waiting' =>
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800',
                                        'in_progress' =>
                                            'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-800',
                                        'completed' =>
                                            'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
                                        'cancelled' =>
                                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800',
                                    ];
                                    $label = [
                                        'waiting' => 'Menunggu',
                                        'in_progress' => 'Sedang Diperiksa',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge[$appt->status] ?? 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800' }}">{{ $label[$appt->status] ?? $appt->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">
                                Belum ada kunjungan hari ini.</td>
                        </tr>
                    @endforelse
                </x-table>
            </div>
        </div>
    @endcan

    @can('manage-emr')
        <div
            class="mt-6 bg-surface-light dark:bg-surface-dark p-6 sm:p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-5 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">10 Diagnosa Terbanyak</h3>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr
                        class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">Kode ICD-10</th>
                        <th class="pb-3 font-semibold">Deskripsi</th>
                        <th class="pb-3 font-semibold">Jumlah</th>
                    </tr>
                </x-slot>
                @forelse($topDiagnoses as $diag)
                    <tr
                        class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 font-mono text-text-primary-light dark:text-text-primary-dark">{{ $diag->icd_code }}
                        </td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $diag->description }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $diag->count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">
                            Belum ada data diagnosa.</td>
                    </tr>
                @endforelse
            </x-table>
        </div>
    @endcan

    @if ($canViewTrends)
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div
                class="bg-surface-light dark:bg-surface-dark p-6 sm:p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm lg:col-span-2">
                <h3 class="mb-5 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Tren Kunjungan &
                    Pendapatan (14 Hari)</h3>
                <div class="h-72">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            <div
                class="bg-surface-light dark:bg-surface-dark p-6 sm:p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
                <h3 class="mb-5 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Kunjungan per Poli (14
                    Hari)</h3>
                <div class="h-72">
                    <canvas id="poliChart"></canvas>
                </div>
            </div>
        </div>
    @endif
@endsection

@if ($canViewTrends)
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                const labels = @json($chart['labels']);
                const revenue = @json($chart['revenueSeries']);
                const visits = @json($chart['visitsSeries']);

                const gridColor = () => getComputedStyle(document.documentElement).getPropertyValue('--chart-grid')
                    .trim() || 'rgba(148,163,184,0.15)';
                const textColor = () => getComputedStyle(document.documentElement).getPropertyValue('--chart-text')
                    .trim() || '#64748b';

                if (window.Chart) {
                    new Chart(document.getElementById('trendChart'), {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                    label: 'Kunjungan',
                                    data: visits,
                                    backgroundColor: '#6366F1',
                                    borderRadius: 6,
                                    order: 2,
                                },
                                {
                                    label: 'Pendapatan (Rp 10rb)',
                                    data: revenue.map(v => Math.round(v / 10000)),
                                    type: 'line',
                                    borderColor: '#06B6D4',
                                    backgroundColor: '#06B6D4',
                                    tension: 0.4,
                                    pointRadius: 3,
                                    order: 1,
                                    yAxisID: 'y1',
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        color: textColor()
                                    }
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: textColor(),
                                        maxRotation: 45,
                                        autoSkip: true
                                    },
                                    grid: {
                                        color: gridColor()
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: textColor(),
                                        precision: 0
                                    },
                                    grid: {
                                        color: gridColor()
                                    }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    ticks: {
                                        color: textColor(),
                                        precision: 0
                                    }
                                },
                            },
                        },
                    });

                    new Chart(document.getElementById('poliChart'), {
                        type: 'doughnut',
                        data: {
                            labels: @json($chart['poliLabels']),
                            datasets: [{
                                data: @json($chart['poliSeries']),
                                backgroundColor: ['#6366F1', '#06B6D4', '#F59E0B', '#10B981', '#EF4444',
                                    '#8B5CF6', '#3B82F6', '#EC4899'
                                ],
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: textColor()
                                    }
                                },
                            },
                        },
                    });
                }
            });
        </script>
    @endpush
@endif