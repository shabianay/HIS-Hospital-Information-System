@extends('layouts.app')
@section('title', 'Kasir / Billing')
@section('content')
    @php
        $statusBadge = [
            'unpaid' => [
                'label' => 'Belum Lunas',
                'color' =>
                    'bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-400 border border-danger-200 dark:border-danger-800',
            ],
            'partial' => [
                'label' => 'Sebagian',
                'color' =>
                    'bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400 border border-warning-200 dark:border-warning-800',
            ],
            'paid' => [
                'label' => 'Lunas',
                'color' =>
                    'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800',
            ],
            'cancelled' => [
                'label' => 'Dibatalkan',
                'color' =>
                    'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800',
            ],
        ];
        $paymentLabels = [
            'cash' => 'Tunai',
            'card' => 'Kartu',
            'qris' => 'QRIS',
            'bpjs' => 'BPJS',
            'insurance' => 'Asuransi',
        ];
    @endphp
    <div
        class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-2xl shadow-glass-sm overflow-hidden">
        <div class="p-6 md:p-8">
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="text-xl font-bold text-text-primary-light dark:text-text-primary-dark">Kasir / Billing Pasien</h3>
                <a href="{{ route('billings.daily-report') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Laporan
                    Harian</a>
            </div>

            <div class="mb-8 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-5 shadow-glass-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Belum Lunas</p>
                    <p class="mt-1 text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $summary['unpaid'] }}</p>
                </div>
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-5 shadow-glass-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Sebagian</p>
                    <p class="mt-1 text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['partial'] }}</p>
                </div>
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-5 shadow-glass-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Lunas</p>
                    <p class="mt-1 text-2xl font-bold text-success-600 dark:text-success-400">{{ $summary['paid'] }}</p>
                </div>
                <div class="rounded-2xl border border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark p-5 shadow-glass-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">Piutang</p>
                    <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">Rp {{ number_format($summary['uncollected'], 0, ',', '.') }}</p>
                </div>
            </div>

            <form method="GET" action="{{ route('billings.index') }}"
                class="mb-8 grid grid-cols-1 md:grid-cols-4 gap-6 p-6 bg-secondary-50 dark:bg-secondary-900/30 rounded-2xl border border-border-light dark:border-border-dark">
                <div>
                    <x-input-label>Filter
                        Tanggal</x-input-label>
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:placeholder:text-text-secondary-dark">
                </div>
                <div>
                    <x-input-label>Filter Status</x-input-label>
                    <select name="status"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Semua Status</option>
                        @foreach ($statusBadge as $key => $st)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $st['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label>Metode Pembayaran</x-input-label>
                    <select name="payment_method"
                        class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                        <option value="">Semua Metode</option>
                        @foreach ($paymentLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="w-full px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold text-sm rounded-xl transition-colors shadow-glass-sm">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('billings.index') }}"
                        class="ml-2 px-6 py-3 bg-white dark:bg-secondary-800 border border-border-light dark:border-border-dark text-text-primary-light dark:text-text-primary-dark font-semibold text-sm rounded-xl hover:bg-secondary-50 dark:hover:bg-secondary-700 transition-colors shadow-glass-sm">
                        Reset
                    </a>
                </div>
            </form>

            <x-table placeholder="Cari no. tagihan / pasien..." class="overflow-hidden">
                <x-slot name="head">
                    <tr
                        class="text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark border-b border-border-light dark:border-border-dark">
                        <th class="pb-4 px-4 font-semibold">No. Tagihan</th>
                        <th class="pb-4 px-4 font-semibold">Pasien</th>
                        <th class="pb-4 px-4 font-semibold">Tanggal Tagihan</th>
                        <th class="pb-4 px-4 font-semibold">Metode</th>
                        <th class="pb-4 px-4 font-semibold">Total Biaya</th>
                        <th class="pb-4 px-4 font-semibold">Dibayar</th>
                        <th class="pb-4 px-4 font-semibold">Status</th>
                        <th class="pb-4 px-4 font-semibold">Aksi</th>
                    </tr>
                </x-slot>
                @forelse($billings as $billing)
                    <tr data-search-row x-show="!search || $el.textContent.toLowerCase().includes(search.toLowerCase())"
                        class="hover:bg-secondary-50 dark:hover:bg-secondary-900/30 transition-colors">
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-mono">
                            {{ $billing->invoice_number }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark font-medium">
                            {{ $billing->appointment?->patient?->name }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            {{ $billing->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            {{ $billing->payment_method ? $paymentLabels[$billing->payment_method] ?? $billing->payment_method : '-' }}
                        </td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp
                            {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">Rp
                            {{ number_format($billing->paid_amount, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            @php $st = $statusBadge[$billing->status] ?? $statusBadge['unpaid']; @endphp
                            <span
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $st['color'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td class="py-4 px-4 text-sm text-text-primary-light dark:text-text-primary-dark">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('billings.show', $billing) }}"
                                    class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Detail</a>
                                @if ($billing->status === 'paid')
                                    <a href="{{ route('billings.receipt', $billing) }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 border border-border-light dark:border-border-dark rounded-lg text-xs font-medium text-text-primary-light dark:text-text-primary-dark hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-all">Kuitansi</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr x-show="!search" data-search-row>
                        <td colspan="8"
                            class="py-12 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada data
                            tagihan.</td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-6">
                {{ $billings->links() }}
            </div>
        </div>
    </div>
@endsection
