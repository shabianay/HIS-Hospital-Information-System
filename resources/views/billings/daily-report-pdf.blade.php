<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Harian Billing</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .sub { color: #475569; margin-bottom: 14px; font-size: 11px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .summary td { border: 1px solid #cbd5e1; padding: 8px; width: 20%; font-size: 11px; }
        .summary .label { display: block; color: #64748b; font-size: 9px; text-transform: uppercase; }
        .summary .value { display: block; font-weight: bold; font-size: 14px; margin-top: 2px; }
        table.detail { width: 100%; border-collapse: collapse; }
        table.detail th, table.detail td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
        table.detail th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
        table.methods { width: 45%; border-collapse: collapse; margin-top: 16px; }
        table.methods th, table.methods td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
        table.methods th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Laporan Harian Billing</h1>
    <div class="sub">Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</div>

    <table class="summary">
        <tr>
            <td><span class="label">Total Pendapatan (Lunas)</span><span class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span></td>
            <td><span class="label">Menunggu</span><span class="value">Rp {{ number_format($totalPending, 0, ',', '.') }}</span></td>
            <td><span class="label">Sebagian</span><span class="value">Rp {{ number_format($totalPartial, 0, ',', '.') }}</span></td>
            <td><span class="label">Jumlah Transaksi</span><span class="value">{{ $totalTransactions }}</span></td>
            <td><span class="label">Transaksi Lunas</span><span class="value">{{ $paidTransactions }}</span></td>
        </tr>
    </table>

    <table class="methods">
        <thead>
            <tr><th>Metode</th><th>Jumlah Transaksi</th><th>Total Nominal</th></tr>
        </thead>
        <tbody>
            @forelse($paymentMethodBreakdown as $method => $data)
            <tr>
                <td>{{ ucfirst($method) }}</td>
                <td>{{ $data['count'] }}</td>
                <td>Rp {{ number_format($data['total'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3">Belum ada transaksi lunas pada tanggal ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="detail" style="margin-top:18px">
        <thead>
            <tr>
                <th>No. Tagihan</th>
                <th>Pasien</th>
                <th>Total</th>
                <th>Dibayar</th>
                <th>Metode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($billings as $billing)
            <tr>
                <td>{{ $billing->invoice_number }}</td>
                <td>{{ $billing->appointment?->patient?->name }}</td>
                <td>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($billing->paid_amount, 0, ',', '.') }}</td>
                <td>{{ $billing->payment_method ? ucfirst($billing->payment_method) : '-' }}</td>
                <td>{{ $billing->status }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Tidak ada tagihan pada tanggal ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>