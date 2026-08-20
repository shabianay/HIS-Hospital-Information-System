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
                <a href="{{ route('billings.reconciliation') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 border border-border-light dark:border-border-dark bg-primary-500 hover:bg-primary-600 text-white text-sm font-semibold rounded-xl shadow-glass-sm hover:shadow-glass-md transition-all duration-200">Rekonsiliasi
                    Kas</a>
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

            <x-filter-form action="{{ route('billings.index') }}">
                <div>
                    <x-input-label for="date">Filter
                        Tanggal</x-input-label>
                    <x-text-input type="date" name="date" id="date" value="{{ request('date') }}" />
                </div>
                <div>
                    <x-input-label for="status">Filter Status</x-input-label>
                    <x-select name="status" id="status">
                        <option value="">Semua Status</option>
                        @foreach ($statusBadge as $key => $st)
                            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>
                                {{ $st['label'] }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <x-input-label for="payment_method">Metode Pembayaran</x-input-label>
                    <x-select name="payment_method" id="payment_method">
                        <option value="">Semua Metode</option>
                        @foreach ($paymentLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('payment_method') === $key ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </x-select>
                </div>
            </x-filter-form>

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
                                <x-action-link href="{{ route('billings.show', $billing) }}">Detail</x-action-link>
                                @if ($billing->status === 'paid')
                                    <x-action-link href="{{ route('billings.receipt', $billing) }}">Kuitansi</x-action-link>
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
