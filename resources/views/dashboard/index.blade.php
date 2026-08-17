@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('patients.index') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark mb-2">Total Pasien Hari
                Ini</div>
            <div class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">
                {{ number_format($totalPatientsToday) }}</div>
        </a>
        <a href="{{ route('appointments.queue') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark mb-2">Total Kunjungan
                Hari Ini</div>
            <div class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">
                {{ number_format($appointmentsToday) }}</div>
        </a>
        <a href="{{ route('billings.daily-report') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark mb-2">Pendapatan Hari
                Ini</div>
            <div class="text-3xl font-bold text-text-primary-light dark:text-text-primary-dark">Rp
                {{ number_format($revenueToday, 0, ',', '.') }}</div>
        </a>
        <a href="{{ route('medicines.stock') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
            <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark mb-2">Stok Obat
                Menipis</div>
            <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ number_format($lowStockCount) }}</div>
        </a>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-4">
        <a href="{{ route('appointments.queue') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Antrian Menunggu</div>
                <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Hari ini</div>
            </div>
            <span class="inline-flex items-center justify-center rounded-xl bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 px-4 py-2 text-2xl font-bold">{{ $waitingQueueToday }}</span>
        </a>
        <a href="{{ route('lab.requests') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Permintaan Lab Pending</div>
                <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Belum selesai</div>
            </div>
            <span class="inline-flex items-center justify-center rounded-xl bg-info-100 text-info-800 dark:bg-info-900/30 dark:text-info-400 px-4 py-2 text-2xl font-bold">{{ $pendingLabCount }}</span>
        </a>
        <a href="{{ route('prescriptions.pending') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Resep Belum Diserahkan</div>
                <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Menunggu dispense</div>
            </div>
            <span class="inline-flex items-center justify-center rounded-xl bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 px-4 py-2 text-2xl font-bold">{{ $pendingPrescriptionsCount }}</span>
        </a>
        <a href="{{ route('billings.index') }}"
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors flex items-center justify-between">
            <div>
                <div class="text-sm font-semibold text-text-secondary-light dark:text-text-secondary-dark">Tagihan Belum Lunas</div>
                <div class="mt-1 text-xs text-text-secondary-light dark:text-text-secondary-dark">Belum / sebagian</div>
            </div>
            <span class="inline-flex items-center justify-center rounded-xl bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 px-4 py-2 text-2xl font-bold">{{ $unpaidBillsCount }}</span>
        </a>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-1">
        <div
            class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Janji Temu Terkini</h3>
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

    <div
        class="mt-8 bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">10 Diagnosa Terbanyak</h3>
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
    </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div
            class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm lg:col-span-2">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Tren Kunjungan &
                Pendapatan (14 Hari)</h3>
            <div class="h-72">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
        <div
            class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h3 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Kunjungan per Poli (14
                Hari)</h3>
            <div class="h-72">
                <canvas id="poliChart"></canvas>
            </div>
        </div>
    </div>
@endsection

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
