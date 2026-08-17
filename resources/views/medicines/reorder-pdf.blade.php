<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekomendasi Pembelian Stok</title>
    <style>
        body { font-family: sans-serif; color: #111827; font-size: 11px; }
        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #111827; padding-bottom: 8px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header p { margin: 2px 0; font-size: 9px; color: #4b5563; }
        .summary { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; font-size: 10px; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REKOMENDASI PEMBELIAN STOK OBAT</h1>
        <p>RUMAH SAKIT HIS — Jl. Kesehatan No. 12, Jakarta</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary">
        <div><strong>Total Obat Perlu Dipesan:</strong> {{ $lowStock->count() }} item</div>
        <div><strong>Total Estimasi Biaya:</strong> Rp {{ number_format($totalSuggestedCost, 0, ',', '.') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Obat</th>
                <th>Satuan</th>
                <th class="right">Stok Saat Ini</th>
                <th class="right">Stok Minimum</th>
                <th class="right">Rekomendasi</th>
                <th class="right">Estimasi Biaya</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lowStock as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="right">{{ $item->total_stock }}</td>
                    <td class="right">{{ $item->minimum_stock }}</td>
                    <td class="right">{{ $item->suggested_quantity }}</td>
                    <td class="right">Rp {{ number_format($item->estimated_cost, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center">Tidak ada obat yang perlu dipesan.</td></tr>
            @endforelse
        </tbody>
        @if($lowStock->isNotEmpty())
            <tfoot>
                <tr class="total">
                    <td colspan="6" class="right">Total</td>
                    <td class="right">Rp {{ number_format($totalSuggestedCost, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>