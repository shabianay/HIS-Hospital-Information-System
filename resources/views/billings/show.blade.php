@extends('layouts.app')
@section('title', 'Detail Tagihan & Pembayaran')
@section('content')
@php
    $statusBadge = [
        'unpaid' => ['label' => 'Belum Lunas', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800'],
        'partial' => ['label' => 'Pembayaran Sebagian', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800'],
        'paid' => ['label' => 'Lunas', 'class' => 'bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400 border border-success-200 dark:border-success-800'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-900/30 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-800'],
    ];
    $paymentLabels = ['cash' => 'Tunai (Cash)', 'card' => 'Kartu Debit/Kredit', 'qris' => 'QRIS / E-Wallet', 'bpjs' => 'BPJS', 'insurance' => 'Asuransi'];
    $paymentOptions = ['cash', 'card', 'qris', 'bpjs', 'insurance'];
    $badge = $statusBadge[$billing->status] ?? $statusBadge['unpaid'];
@endphp
<div class="mx-auto max-w-4xl space-y-6" x-data="{ total: {{ $billing->total_amount }}, amountPaid: 0, get change() { return Math.max(0, this.amountPaid - this.total); } }">
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-border-light dark:border-border-dark">
            <div>
                <h2 class="text-2xl font-bold text-text-primary-light dark:text-text-primary-dark">Detail Tagihan</h2>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mt-1">No. Invoice: <span class="font-mono">{{ $billing->invoice_number }}</span></p>
            </div>
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $badge['class'] }}">{{ $badge['label'] }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 text-sm">
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Pasien</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $billing->appointment?->patient?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Dokter</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $billing->appointment?->doctor?->name }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Tanggal Tagihan</span>
                <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $billing->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-text-secondary-light dark:text-text-secondary-dark uppercase mb-1">Metode Pembayaran</span>
                <span class="block text-base font-medium capitalize text-text-primary-light dark:text-text-primary-dark">{{ $billing->payment_method ? ($paymentLabels[$billing->payment_method] ?? $billing->payment_method) : '-' }}</span>
            </div>
        </div>

        <div class="mt-8 overflow-x-auto border-t border-border-light dark:border-border-dark pt-6">
            <x-table :searchable="false">
                <x-slot name="head">
                    <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                        <th class="pb-3 font-semibold">Rincian Item</th>
                        <th class="pb-3 font-semibold text-right">Biaya</th>
                    </tr>
                </x-slot>
                @forelse($billing->billingItems as $item)
                    <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">{{ $item->description }} <span class="text-xs text-text-secondary-light dark:text-text-secondary-dark">({{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }})</span></td>
                        <td class="py-3 text-right text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" class="py-4 text-center text-text-secondary-light dark:text-text-secondary-dark">Tidak ada rincian item.</td></tr>
                    @endforelse
                    <tr class="border-b border-border-light dark:border-border-dark font-semibold">
                        <td class="py-3 text-primary-800 dark:text-primary-400">Diskon</td>
                        <td class="py-3 text-right text-red-600 dark:text-red-400">- Rp {{ number_format($billing->discount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="font-bold">
                        <td class="py-3 text-primary-800 dark:text-primary-400">Total Tagihan</td>
                        <td class="py-3 text-right text-primary-800 dark:text-primary-400">Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-3 text-text-primary-light dark:text-text-primary-dark">Dibayar</td>
                        <td class="py-3 text-right text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($billing->status !== 'paid' && $billing->status !== 'cancelled')
                    <tr class="font-semibold">
                        <td class="py-3 text-red-700 dark:text-red-400">Sisa Tagihan</td>
                        <td class="py-3 text-right text-red-700 dark:text-red-400">Rp {{ number_format(max(0, $billing->total_amount - $billing->paid_amount), 0, ',', '.') }}</td>
                    </tr>
                    @endif
            </x-table>
        </div>

        <div class="mt-6 flex justify-end gap-3 border-t border-border-light dark:border-border-dark pt-6">
            <x-secondary-button href="{{ route('billings.index') }}">Kembali</x-secondary-button>
            @if($billing->status === 'paid')
                <a href="{{ route('billings.receipt', $billing) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-glass-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-surface-dark transition-all duration-200">Cetak Kuitansi</a>
                <a href="{{ route('billings.receipt.pdf', $billing) }}" class="inline-flex items-center gap-2 rounded-xl border border-border-light bg-surface-light px-5 py-2.5 text-sm font-semibold text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-all duration-200">Download PDF</a>
            @endif
        </div>
    </div>

    @if(!in_array($billing->status, ['paid', 'cancelled']))
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-6 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Proses Pembayaran</h3>
        <form action="{{ route('billings.payment', $billing) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="grid grid-cols-1 gap-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="payment_method" value="Metode Pembayaran" />
                        <select name="payment_method" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                            <option value="">Pilih Metode</option>
                            @foreach($paymentLabels as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method', $billing->payment_method) === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('payment_method')" />
                    </div>
                    <div>
                        <x-input-label for="paid_amount" value="Nominal Dibayar (Rp)" />
                        <x-text-input type="number" x-model.number="amountPaid" name="paid_amount" min="0" placeholder="Total tagihan: {{ number_format($billing->total_amount, 0, ',', '.') }}" required />
                        <x-input-error :messages="$errors->get('paid_amount')" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-5 text-sm">
                    <span class="font-medium text-text-primary-light dark:text-text-primary-dark">Kembalian:</span>
                    <strong class="text-lg text-primary-800 dark:text-primary-400" x-text="'Rp ' + Number(change).toLocaleString('id-ID')">Rp 0</strong>
                </div>

                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">
                    Catatan: jika nominal dibayar kurang dari total, tagihan dicatat sebagai pembayaran sebagian (partial).
                </p>

                <div class="flex justify-end gap-3 border-t border-border-light dark:border-border-dark pt-6">
                    <x-primary-button type="submit">Proses Pembayaran</x-primary-button>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
