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
<div class="mx-auto max-w-4xl space-y-6">
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
                @if($billing->payments->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($billing->payment_breakdown as $method => $info)
                            <span class="inline-flex items-center gap-1 rounded-full bg-secondary-100 dark:bg-secondary-900/30 px-3 py-1 text-xs font-semibold text-text-primary-light dark:text-text-primary-dark border border-border-light dark:border-border-dark">
                                {{ $paymentLabels[$method] ?? $method }}
                                <span class="text-text-secondary-light dark:text-text-secondary-dark">Rp {{ number_format($info['total'], 0, ',', '.') }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <span class="block text-base font-medium text-text-primary-light dark:text-text-primary-dark">{{ $billing->payment_method ? ($paymentLabels[$billing->payment_method] ?? $billing->payment_method) : '-' }}</span>
                @endif
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

    @if($billing->payments->isNotEmpty())
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-6 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Riwayat Pembayaran</h3>
        <x-table :searchable="false">
            <x-slot name="head">
                <tr class="border-b border-border-light dark:border-border-dark text-xs uppercase tracking-wider text-text-secondary-light dark:text-text-secondary-dark">
                    <th class="pb-3 font-semibold">Metode</th>
                    <th class="pb-3 font-semibold text-right">Nominal</th>
                    <th class="pb-3 font-semibold">Referensi</th>
                    <th class="pb-3 font-semibold">Diproses Oleh</th>
                    <th class="pb-3 font-semibold">Waktu</th>
                </tr>
            </x-slot>
            @foreach($billing->payments as $payment)
                <tr class="border-b border-border-light dark:border-border-dark hover:bg-primary-50/50 dark:hover:bg-primary-900/10 transition-colors">
                    <td class="py-3 text-sm font-medium text-text-primary-light dark:text-text-primary-dark">{{ $paymentLabels[$payment->payment_method] ?? $payment->payment_method }}</td>
                    <td class="py-3 text-right text-sm text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td class="py-3 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $payment->reference ?: '-' }}</td>
                    <td class="py-3 text-sm text-text-primary-light dark:text-text-primary-dark">{{ $payment->processedBy?->name ?: '-' }}</td>
                    <td class="py-3 text-sm text-text-secondary-light dark:text-text-secondary-dark">{{ $payment->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </x-table>
    </div>
    @endif

    @if(!in_array($billing->status, ['paid', 'cancelled']))
    <div class="bg-surface-light dark:bg-surface-dark p-8 rounded-2xl border border-border-light dark:border-border-dark shadow-glass-sm">
        <h3 class="mb-6 text-lg font-bold text-text-primary-light dark:text-text-primary-dark">Proses Pembayaran</h3>
        @php
            $remainingAmount = max(0, (float) $billing->total_amount - (float) $billing->paid_amount);
        @endphp
        <form action="{{ route('billings.payment', $billing) }}" method="POST" x-data="{ payments: [{ method: 'cash', amount: {{ $remainingAmount }}, reference: '' }], totalPaid: {{ $remainingAmount }}, get remaining() { return Math.max(0, {{ $remainingAmount }} - this.totalPaid); } }">
            @csrf
            @method('PATCH')
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <x-input-label value="Rincian Pembayaran" />
                        <button type="button" @click="payments.push({ method: 'cash', amount: 0, reference: '' })" class="text-sm font-medium text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300">+ Tambah Metode</button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(payment, index) in payments" :key="index">
                            <div class="grid grid-cols-12 gap-3 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-4">
                                <div class="col-span-12 md:col-span-4">
                                    <x-input-label for="payments[][method]" value="Metode" />
                                    <select name="payments[][method]" x-model="payment.method" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                                        @foreach($paymentLabels as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-8 md:col-span-4">
                                    <x-input-label for="payments[][amount]" value="Nominal (Rp)" />
                                    <input type="number" step="0.01" min="0" name="payments[][amount]" x-model.number="payment.amount" @input="totalPaid = payments.reduce((sum, p) => sum + (Number(p.amount) || 0), 0)" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark" required>
                                </div>
                                <div class="col-span-4 md:col-span-3">
                                    <x-input-label for="payments[][reference]" value="Referensi" />
                                    <input type="text" name="payments[][reference]" x-model="payment.reference" placeholder="Opsional" class="w-full border border-border-light bg-surface-light px-4 py-3 text-sm text-text-primary-light placeholder:text-text-secondary-light focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 focus:outline-none rounded-xl shadow-glass-sm transition-all duration-200 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark">
                                </div>
                                <div class="flex items-end justify-end md:col-span-1">
                                    <button type="button" @click="payments.splice(index, 1)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('payments')" />
                </div>

                <div class="grid grid-cols-1 gap-4 rounded-xl border border-border-light dark:border-border-dark bg-primary-50/50 dark:bg-primary-900/10 p-5 text-sm md:grid-cols-3">
                    <div>
                        <span class="font-medium text-text-secondary-light dark:text-text-secondary-dark">Sisa Tagihan:</span>
                        <strong class="ml-1 text-text-primary-light dark:text-text-primary-dark">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="font-medium text-text-secondary-light dark:text-text-secondary-dark">Total Dibayar:</span>
                        <strong class="ml-1 text-primary-800 dark:text-primary-400" x-text="'Rp ' + Number(totalPaid).toLocaleString('id-ID')">Rp 0</strong>
                    </div>
                    <div>
                        <span class="font-medium text-text-secondary-light dark:text-text-secondary-dark">Sisa Setelah Bayar:</span>
                        <strong class="ml-1 text-danger-600 dark:text-danger-400" x-text="'Rp ' + Number(remaining).toLocaleString('id-ID')">Rp 0</strong>
                    </div>
                </div>

                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark">
                    Total pembayaran tidak boleh melebihi sisa tagihan. Jika kurang dari sisa, tagihan dicatat sebagai pembayaran sebagian (partial).
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
