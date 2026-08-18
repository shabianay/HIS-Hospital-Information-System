@extends('layouts.app')
@section('title', 'Laporan & Statistik')
@section('content')
<div class="space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Laporan & Statistik</h3>
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="date" name="start" value="{{ $start->format('Y-m-d') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <span class="text-text-secondary-light dark:text-text-secondary-dark">s/d</span>
                <input type="date" name="end" value="{{ $end->format('Y-m-d') }}" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <select name="period" class="border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                    <option value="harian" {{ $period === 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="mingguan" {{ $period === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ $period === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                </select>
                <x-primary-button type="submit">Tampilkan</x-primary-button>
                <a href="{{ route('reports.pdf', ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d'), 'period' => $period]) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Export PDF</a>
                <a href="{{ route('reports.csv', ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d'), 'period' => $period]) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">Export CSV</a>
            </form>
        </div>
        <div class="mb-6 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm">
            <span class="font-medium text-text-secondary-light dark:text-text-secondary-dark">Periode:</span>
            <strong class="text-text-primary-light dark:text-text-primary-dark">{{ $start->translatedFormat('d M Y') }} — {{ $end->translatedFormat('d M Y') }}</strong>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-xl bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800">
                <div class="text-sm text-success-700 dark:text-success-400">Pendapatan (Lunas)</div>
                <div class="text-xl font-bold text-success-900 dark:text-success-300">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="text-sm text-yellow-700 dark:text-yellow-400">Belum Dibayar</div>
                <div class="text-xl font-bold text-yellow-900 dark:text-yellow-300">Rp {{ number_format($pendingRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl bg-primary-50 dark:bg-primary-900/20 p-4 border border-primary-200 dark:border-primary-800">
                <div class="text-sm text-primary-700 dark:text-primary-400">Total Kunjungan</div>
                <div class="text-xl font-bold text-primary-900 dark:text-primary-300">{{ number_format($totalVisits) }}</div>
            </div>
            <div class="rounded-xl bg-secondary-50 dark:bg-secondary-900/20 p-4 border border-secondary-200 dark:border-secondary-800">
                <div class="text-sm text-secondary-700 dark:text-secondary-400">Kunjungan Selesai</div>
                <div class="text-xl font-bold text-secondary-900 dark:text-secondary-300">{{ number_format($completedVisits) }}</div>
            </div>
            <div class="rounded-xl bg-info-50 dark:bg-info-900/20 p-4 border border-info-200 dark:border-info-800">
                <div class="text-sm text-info-700 dark:text-info-400">Pasien Baru</div>
                <div class="text-xl font-bold text-info-900 dark:text-info-300">{{ number_format($newPatients) }}</div>
            </div>
            <div class="rounded-xl bg-warning-50 dark:bg-warning-900/20 p-4 border border-warning-200 dark:border-warning-800">
                <div class="text-sm text-warning-700 dark:text-warning-400">Total Tagihan</div>
                <div class="text-xl font-bold text-warning-900 dark:text-warning-300">{{ number_format($totalBilling) }}</div>
            </div>
            <div class="rounded-xl bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800">
                <div class="text-sm text-success-700 dark:text-success-400">Tagihan Lunas</div>
                <div class="text-xl font-bold text-success-900 dark:text-success-300">{{ number_format($paidBilling) }}</div>
            </div>
            <div class="rounded-xl bg-secondary-50 dark:bg-secondary-900/20 p-4 border border-secondary-200 dark:border-secondary-800">
                <div class="text-sm text-secondary-700 dark:text-secondary-400">Permintaan Lab</div>
                <div class="text-xl font-bold text-secondary-900 dark:text-secondary-300">{{ number_format($labTotal) }}</div>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h4 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Tren Pendapatan & Kunjungan per Periode</h4>
            <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 dark:bg-primary-900/30 px-4 py-1.5 text-sm font-semibold text-primary-800 dark:text-primary-400">
                Agregasi: {{ $period === 'mingguan' ? 'Mingguan' : ($period === 'bulanan' ? 'Bulanan' : 'Harian') }}
            </span>
        </div>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Periode</th>
                    <th class="pb-3 font-semibold">Pendapatan (Lunas)</th>
                    <th class="pb-3 font-semibold">Kunjungan</th>
                    <th class="pb-3 font-semibold">Selesai</th>
                    <th class="pb-3 font-semibold">Pasien Baru</th>
                </tr>
            </x-slot>
            @forelse($periodRows as $row)
            <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $row['label'] }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row['visits']) }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row['completed']) }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row['new_patients']) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
            @endforelse
        </x-table>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Kunjungan per Poli</h4>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">Poli</th>
                        <th class="pb-3 font-semibold">Kunjungan</th>
                    </tr>
                </x-slot>
                @forelse($poliVisits as $row)
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $row->name }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row->total) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
                @endforelse
            </x-table>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Produktivitas Dokter</h4>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">Dokter</th>
                        <th class="pb-3 font-semibold">Kunjungan</th>
                        <th class="pb-3 font-semibold">Selesai</th>
                        <th class="pb-3 font-semibold">Pendapatan</th>
                    </tr>
                </x-slot>
                @forelse($doctorProductivity as $row)
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $row->doctor_name }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row->total_visits) }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row->completed) }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
                @endforelse
            </x-table>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">10 Diagnosis Terbanyak</h4>        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Kode ICD-10</th>
                    <th class="pb-3 font-semibold">Deskripsi</th>
                    <th class="pb-3 font-semibold">Jumlah</th>
                </tr>
            </x-slot>
            @forelse($topDiagnoses as $row)
            <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-3 font-mono text-text-primary-light dark:text-text-primary-dark">{{ $row->icd_code }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $row->description }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row->total) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
            @endforelse
        </x-table>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h4 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Nilai Stok Obat (Saat Ini)</h4>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 dark:bg-primary-900/30 px-4 py-1.5 font-semibold text-primary-800 dark:text-primary-400">
                    Total Nilai: Rp {{ number_format($stockValuationTotal, 0, ',', '.') }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-warning-100 dark:bg-warning-900/30 px-4 py-1.5 font-semibold text-warning-800 dark:text-warning-400">
                    Kedaluwarsa ≤60 hari: {{ number_format($expiringStockCount) }}
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-danger-100 dark:bg-danger-900/30 px-4 py-1.5 font-semibold text-danger-800 dark:text-danger-400">
                    Stok Kritis: {{ number_format($lowStockCount) }}
                </span>
            </div>
        </div>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Obat</th>
                    <th class="pb-3 font-semibold">Stok</th>
                    <th class="pb-3 font-semibold">Harga Beli</th>
                    <th class="pb-3 font-semibold text-right">Nilai</th>
                </tr>
            </x-slot>
            @forelse($stockValuation as $row)
            <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $row->name }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row->total_quantity) }} {{ $row->unit }}</td>
                <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($row->buy_price, 0, ',', '.') }}</td>
                <td class="py-3 text-right text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($row->total_value, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
            @endforelse
        </x-table>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Konsumsi Obat</h4>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">Obat</th>
                        <th class="pb-3 font-semibold">Jumlah</th>
                        <th class="pb-3 font-semibold">Nilai</th>
                    </tr>
                </x-slot>
                @forelse($medicineConsumption as $row)
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $row->name }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($row->total_quantity) }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($row->total_value, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
                @endforelse
            </x-table>
        </div>

        <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
            <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Beban Kerja Laboratorium</h4>
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">Status</th>
                        <th class="pb-3 font-semibold">Jumlah</th>
                    </tr>
                </x-slot>
                @php $statusLabels = ['pending' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan']; @endphp
                @forelse(['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $statusLabels[$status] }}</td>
                    <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ number_format($labWorkload[$status]->total ?? 0) }}</td>
                </tr>
                @empty
                <tr><td colspan="2" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data.</td></tr>
                @endforelse
            </x-table>
        </div>
    </div>
</div>
@endsection