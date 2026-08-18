<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan & Statistik</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 17px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 14px; font-size: 10px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .summary td { border: 1px solid #cbd5e1; padding: 6px; width: 12.5%; font-size: 10px; text-align: center; }
        .summary .label { display: block; color: #64748b; font-size: 8px; text-transform: uppercase; }
        .summary .value { display: block; font-weight: bold; font-size: 12px; margin-top: 2px; }
        h2 { font-size: 12px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin: 14px 0 6px; }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: left; }
        table.data th { background: #f1f5f9; font-size: 8px; text-transform: uppercase; }
        .footer { margin-top: 18px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Laporan & Statistik</h1>
    <div class="sub">Periode: {{ $start->translatedFormat('d M Y') }} — {{ $end->translatedFormat('d M Y') }} · Agregasi: {{ $period === 'mingguan' ? 'Mingguan' : ($period === 'bulanan' ? 'Bulanan' : 'Harian') }} · Dicetak: {{ $generatedAt }}</div>

    <table class="summary">
        <tr>
            <td><span class="label">Pendapatan</span><span class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span></td>
            <td><span class="label">Belum Bayar</span><span class="value">Rp {{ number_format($pendingRevenue, 0, ',', '.') }}</span></td>
            <td><span class="label">Kunjungan</span><span class="value">{{ number_format($totalVisits) }}</span></td>
            <td><span class="label">Selesai</span><span class="value">{{ number_format($completedVisits) }}</span></td>
            <td><span class="label">Pasien Baru</span><span class="value">{{ number_format($newPatients) }}</span></td>
            <td><span class="label">Tagihan</span><span class="value">{{ number_format($totalBilling) }}</span></td>
            <td><span class="label">Lunas</span><span class="value">{{ number_format($paidBilling) }}</span></td>
            <td><span class="label">Permintaan Lab</span><span class="value">{{ number_format($labTotal) }}</span></td>
        </tr>
    </table>

    <h2>Tren Pendapatan &amp; Kunjungan per Periode</h2>
    <table class="data">
        <thead><tr><th>Periode</th><th>Pendapatan (Lunas)</th><th>Kunjungan</th><th>Selesai</th><th>Pasien Baru</th></tr></thead>
        <tbody>
            @forelse($periodRows as $row)
            <tr><td>{{ $row['label'] }}</td><td>Rp {{ number_format($row['revenue'], 0, ',', '.') }}</td><td>{{ number_format($row['visits']) }}</td><td>{{ number_format($row['completed']) }}</td><td>{{ number_format($row['new_patients']) }}</td></tr>
            @empty
            <tr><td colspan="5">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Kunjungan per Poli</h2>
    <table class="data">
        <thead><tr><th>Poli</th><th>Kunjungan</th></tr></thead>
        <tbody>
            @forelse($poliVisits as $row)
            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total) }}</td></tr>
            @empty
            <tr><td colspan="2">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Produktivitas Dokter</h2>
    <table class="data">
        <thead><tr><th>Dokter</th><th>Kunjungan</th><th>Selesai</th><th>Pendapatan</th></tr></thead>
        <tbody>
            @forelse($doctorProductivity as $row)
            <tr><td>{{ $row->doctor_name }}</td><td>{{ number_format($row->total_visits) }}</td><td>{{ number_format($row->completed) }}</td><td>Rp {{ number_format($row->revenue, 0, ',', '.') }}</td></tr>
            @empty
            <tr><td colspan="4">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>10 Diagnosis Terbanyak</h2>
    <table class="data">
        <thead><tr><th>Kode ICD-10</th><th>Deskripsi</th><th>Jumlah</th></tr></thead>
        <tbody>
            @forelse($topDiagnoses as $row)
            <tr><td>{{ $row->icd_code }}</td><td>{{ $row->description }}</td><td>{{ number_format($row->total) }}</td></tr>
            @empty
            <tr><td colspan="3">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Nilai Stok Obat (Saat Ini)</h2>
    <table class="data">
        <thead><tr><th>Obat</th><th>Stok</th><th>Harga Beli</th><th>Nilai</th></tr></thead>
        <tbody>
            @forelse($stockValuation as $row)
            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_quantity) }} {{ $row->unit }}</td><td>Rp {{ number_format($row->buy_price, 0, ',', '.') }}</td><td>Rp {{ number_format($row->total_value, 0, ',', '.') }}</td></tr>
            @empty
            <tr><td colspan="4">Tidak ada data.</td></tr>
            @endforelse
            <tr><td colspan="3"><strong>Total Nilai Stok</strong></td><td><strong>Rp {{ number_format($stockValuationTotal, 0, ',', '.') }}</strong></td></tr>
            <tr><td colspan="3"><strong>Batch Kedaluwarsa ≤60 hari</strong></td><td><strong>{{ number_format($expiringStockCount) }}</strong></td></tr>
            <tr><td colspan="3"><strong>Obat Stok Kritis</strong></td><td><strong>{{ number_format($lowStockCount) }}</strong></td></tr>
        </tbody>
    </table>

    <h2>Konsumsi Obat</h2>
    <table class="data">
        <thead><tr><th>Obat</th><th>Jumlah</th><th>Nilai</th></tr></thead>
        <tbody>
            @forelse($medicineConsumption as $row)
            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total_quantity) }}</td><td>Rp {{ number_format($row->total_value, 0, ',', '.') }}</td></tr>
            @empty
            <tr><td colspan="3">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Beban Kerja Laboratorium</h2>
    <table class="data">
        <thead><tr><th>Status</th><th>Jumlah</th></tr></thead>
        <tbody>
            @foreach(['pending' => 'Menunggu', 'in_progress' => 'Dikerjakan', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $status => $label)
            <tr><td>{{ $label }}</td><td>{{ number_format($labWorkload[$status]->total ?? 0) }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Dokumen dihasilkan oleh sistem HIS.</div>
</body>
</html>