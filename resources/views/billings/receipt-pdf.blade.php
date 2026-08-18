<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran #{{ $billing->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; color: #111827; font-size: 11px; }
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #111827; padding-bottom: 8px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 2px 0; font-size: 9px; color: #4b5563; }
        .info { font-size: 10px; margin-bottom: 12px; }
        .info div { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .items th, .items td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; font-size: 10px; }
        .items th { background: #f3f4f6; }
        .total { border-top: 2px solid #111827; margin-top: 6px; padding-top: 6px; }
        .footer { margin-top: 18px; border-top: 1px dashed #9ca3af; padding-top: 6px; text-align: center; font-size: 9px; color: #6b7280; }
        .note { margin-top: 10px; padding: 6px; background: #f9fafb; border: 1px solid #e5e7eb; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RUMAH SAKIT HIS</h1>
        <p>Jl. Kesehatan No. 12, Jakarta</p>
        <p>Telp: (021) 1234567</p>
    </div>

    <div class="info">
        <div><span>No. Tagihan</span><strong>{{ $billing->invoice_number }}</strong></div>
        <div><span>Tanggal</span><span>{{ $billing->created_at?->format('d/m/Y H:i') }}</span></div>
        <div><span>Pasien</span><strong>{{ $billing->appointment?->patient?->name }}</strong></div>
        <div><span>NIK</span><span>{{ $billing->appointment?->patient?->nik }}</span></div>
        <div><span>Dokter</span><span>{{ $billing->appointment?->doctor?->name }}</span></div>
        <div><span>No. Antrian</span><span>{{ $billing->appointment?->queue_number }}</span></div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th style="text-align:right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($billing->billingItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align:right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Tidak ada rincian item</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        <div class="row"><span>Subtotal</span><span>Rp {{ number_format($billing->total_amount + $billing->discount, 0, ',', '.') }}</span></div>
        @if ($billing->discount > 0)
            <div class="row"><span>Diskon</span><span>- Rp {{ number_format($billing->discount, 0, ',', '.') }}</span></div>
        @endif
        <div class="row" style="font-weight:bold"><span>Total Tagihan</span><span>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</span></div>
        <div class="row"><span>Dibayar</span><span>Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</span></div>
        <div class="row"><span>Metode</span><span>
            @if($billing->payments->isNotEmpty())
                @foreach($billing->payment_breakdown as $method => $info)
                    {{ $paymentLabels[$method] ?? $method }} (Rp {{ number_format($info['total'], 0, ',', '.') }})@if(!$loop->last), @endif
                @endforeach
            @else
                {{ $paymentLabels[$billing->payment_method] ?? ($billing->payment_method ?: '-') }}
            @endif
        </span></div>
        @if ($billing->status !== 'paid')
            <div class="row" style="font-weight:bold; color:#b91c1c"><span>Sisa Tagihan</span><span>Rp {{ number_format(max(0, $billing->total_amount - $billing->paid_amount), 0, ',', '.') }}</span></div>
        @endif
    </div>

    @if ($billing->notes)
        <div class="note"><strong>Catatan:</strong> {{ $billing->notes }}</div>
    @endif

    <div class="footer">
        <p>Terima Kasih Atas Kunjungan Anda</p>
        <p>Semoga Lekas Sembuh</p>
    </div>
</body>
</html>