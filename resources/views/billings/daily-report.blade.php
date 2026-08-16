@extends('layouts.app')
@section('title', 'Laporan Harian Billing')
@section('content')
@php
    $paymentLabels = ['cash' => 'Tunai', 'card' => 'Kartu', 'qris' => 'QRIS', 'bpjs' => 'BPJS', 'insurance' => 'Asuransi'];
    $statusBadge = [
        'unpaid' => ['label' => 'Belum Lunas', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800'],
        'partial' => ['label' => 'Sebagian', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800'],
        'paid' => ['label' => 'Lunas', 'class' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800'],
    ];
@endphp
<div class="space-y-6">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Laporan Harian Billing</h3>
            <form method="GET" action="{{ route('billings.daily-report') }}" class="flex items-center gap-3">
                <input type="date" name="date" value="{{ $date }}" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                <x-primary-button type="submit">Tampilkan</x-primary-button>
                <a href="{{ route('billings.daily-report.pdf', ['date' => $date]) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border border-border-light bg-surface-light text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-colors">PDF</a>
            </form>
        </div>

        <div class="mb-6 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-4 text-sm">
            <span class="font-medium text-text-secondary-light dark:text-text-secondary-dark">Tanggal Laporan:</span>
            <strong class="text-text-primary-light dark:text-text-primary-dark">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <div class="rounded-xl bg-success-50 dark:bg-success-900/20 p-4 border border-success-200 dark:border-success-800">
                <div class="text-sm text-success-700 dark:text-success-400">Total Pendapatan (Lunas)</div>
                <div class="text-xl font-bold text-success-900 dark:text-success-300">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                <div class="text-sm text-red-700 dark:text-red-400">Menunggu (Belum Lunas)</div>
                <div class="text-xl font-bold text-red-900 dark:text-red-300">Rp {{ number_format($totalPending, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="text-sm text-yellow-700 dark:text-yellow-400">Pembayaran Sebagian</div>
                <div class="text-xl font-bold text-yellow-900 dark:text-yellow-300">Rp {{ number_format($totalPartial, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-xl bg-primary-50 dark:bg-primary-900/20 p-4 border border-primary-200 dark:border-primary-800">
                <div class="text-sm text-primary-700 dark:text-primary-400">Jumlah Transaksi</div>
                <div class="text-xl font-bold text-primary-900 dark:text-primary-300">{{ $totalTransactions }}</div>
            </div>
            <div class="rounded-xl bg-secondary-50 dark:bg-secondary-900/20 p-4 border border-secondary-200 dark:border-secondary-800">
                <div class="text-sm text-secondary-700 dark:text-secondary-400">Transaksi Lunas</div>
                <div class="text-xl font-bold text-secondary-900 dark:text-secondary-300">{{ $paidTransactions }}</div>
            </div>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Rincian Metode Pembayaran (Transaksi Lunas)</h4>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Metode</th>
                    <th class="pb-3 font-semibold">Jumlah Transaksi</th>
                    <th class="pb-3 font-semibold">Total Nominal</th>
                </tr>
            </x-slot>
            @forelse($paymentMethodBreakdown as $method => $data)
                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 capitalize text-text-primary-light dark:text-text-primary-dark">{{ $paymentLabels[$method] ?? $method }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $data['count'] }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Belum ada transaksi lunas pada tanggal ini.</td></tr>
                    @endforelse
            </x-table>
        </div>
    </div>

    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h4 class="mb-4 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Daftar Tagihan {{ $date }}</h4>
        <x-table placeholder="Cari no. tagihan / pasien..." class="overflow-hidden">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">No. Tagihan</th>
                    <th class="pb-3 font-semibold">Pasien</th>
                    <th class="pb-3 font-semibold">Total</th>
                    <th class="pb-3 font-semibold">Dibayar</th>
                    <th class="pb-3 font-semibold">Metode</th>
                    <th class="pb-3 font-semibold">Status</th>
                </tr>
            </x-slot>
            @forelse($billings as $billing)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())" class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 font-mono text-text-primary-light dark:text-text-primary-dark">{{ $billing->invoice_number }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $billing->appointment?->patient?->name }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</td>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $billing->payment_method ? ($paymentLabels[$billing->payment_method] ?? $billing->payment_method) : '-' }}</td>
                        <td class="py-3">
                            @php $st = $statusBadge[$billing->status] ?? $statusBadge['unpaid']; @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['class'] }}">{{ $st['label'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr x-show="!search" data-search-row><td colspan="6" class="py-6 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada tagihan pada tanggal ini.</td></tr>
                    @endforelse
            </x-table>
        </div>
    </div>

    <div class="flex justify-end">
        <x-secondary-button href="{{ route('billings.index') }}">Kembali ke Billing</x-secondary-button>
    </div>
</div>
@endsection
