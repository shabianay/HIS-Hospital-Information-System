<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran #{{ $billing->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
<div class="mx-auto max-w-md bg-white p-6 shadow-md border border-gray-200">
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-900">RUMAH SAKIT HIS</h2>
        <p class="text-xs text-gray-500">Jl. Kesehatan No. 12, Jakarta</p>
        <p class="text-xs text-gray-500">Telp: (021) 1234567</p>
    </div>

    <div class="mb-4 space-y-1 border-b border-dashed border-gray-300 pb-4 text-xs">
        <div class="flex justify-between"><span>No. Tagihan:</span><strong>{{ $billing->invoice_number }}</strong></div>
        <div class="flex justify-between"><span>Tanggal:</span><span>{{ $billing->created_at?->format('d/m/Y H:i') }}</span></div>
        <div class="flex justify-between"><span>Pasien:</span><strong>{{ $billing->appointment?->patient?->name }}</strong></div>
        <div class="flex justify-between"><span>NIK:</span><span>{{ $billing->appointment?->patient?->nik }}</span></div>
        <div class="flex justify-between"><span>Dokter:</span><span>{{ $billing->appointment?->doctor?->name }}</span></div>
        <div class="flex justify-between"><span>No. Antrian:</span><span>{{ $billing->appointment?->queue_number }}</span></div>
    </div>

    <div class="mb-4 space-y-2 border-b border-dashed border-gray-300 pb-4 text-xs">
        @forelse($billing->billingItems as $item)
        <div class="flex justify-between">
            <span>{{ $item->description }}</span>
            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
        @empty
        <div class="flex justify-between">
            <span>Tidak ada rincian item</span>
            <span>-</span>
        </div>
        @endforelse
    </div>

    <div class="space-y-1 text-xs">
        <div class="flex justify-between"><span>Subtotal:</span><span>Rp {{ number_format($billing->total_amount + $billing->discount, 0, ',', '.') }}</span></div>
        @if($billing->discount > 0)
        <div class="flex justify-between"><span>Diskon:</span><span>- Rp {{ number_format($billing->discount, 0, ',', '.') }}</span></div>
        @endif
        <div class="flex justify-between font-bold"><span>Total Tagihan:</span><span>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</span></div>
        <div class="flex justify-between"><span>Dibayar:</span><span>Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</span></div>
        <div class="flex justify-between">
            <span>Metode:</span>
            <span class="capitalize">{{ $billing->payment_method ?: '-' }}</span>
        </div>
        @if($billing->status !== 'paid')
        <div class="flex justify-between font-bold text-red-600"><span>Sisa Tagihan:</span><span>Rp {{ number_format(max(0, $billing->total_amount - $billing->paid_amount), 0, ',', '.') }}</span></div>
        @endif
    </div>

    <div class="mt-8 border-t border-dashed border-gray-300 pt-4 text-center text-xs text-gray-500">
        <p>Terima Kasih Atas Kunjungan Anda</p>
        <p>Semoga Lekas Sembuh</p>
    </div>

    @if($billing->notes)
    <div class="mt-4 rounded-lg bg-gray-50 p-3 text-xs text-gray-600 border border-gray-200">
        <strong>Catatan:</strong> {{ $billing->notes }}
    </div>
    @endif

    <div class="no-print mt-6 flex justify-center gap-2">
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-xs font-semibold text-white shadow hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all duration-200">Cetak Kuitansi</button>
        <a href="{{ route('billings.show', $billing) }}" class="inline-flex items-center gap-2 rounded-xl border border-border-light bg-surface-light px-5 py-2.5 text-xs font-semibold text-text-primary-light hover:bg-primary-50 dark:border-border-dark dark:bg-surface-dark dark:text-text-primary-dark dark:hover:bg-primary-900/10 transition-all duration-200">Kembali</a>
    </div>
</div>
</body>
</html>
