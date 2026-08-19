<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Radiologi</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 16px; font-size: 11px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10px; }
        .info .label { color: #64748b; font-size: 9px; text-transform: uppercase; }
        .info .value { font-weight: bold; font-size: 11px; }
        .urgent { display: inline-block; background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 2px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        table.results { width: 100%; border-collapse: collapse; }
        table.results th, table.results td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.results th { background: #f1f5f9; font-size: 9px; text-transform: uppercase; }
        .abnormal { color: #b91c1c; font-weight: bold; }
        .normal { color: #15803d; }
        .notes { margin-top: 14px; font-size: 10px; color: #475569; }
        .footer { margin-top: 24px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <h1>Hasil Pemeriksaan Radiologi</h1>
    <div class="sub">Dicetak: {{ $generatedAt }} @if($radiologyRequest->is_urgent)<span class="urgent">PRIORITAS</span>@endif</div>

    <table class="info">
        <tr>
            <td><span class="label">Pasien</span><span class="value">{{ $radiologyRequest->patient?->name ?? '-' }}</span></td>
            <td><span class="label">No. Antrian</span><span class="value">{{ $radiologyRequest->appointment?->queue_number ?? '-' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Perujuk</span><span class="value">{{ $radiologyRequest->doctor?->name ?? '-' }}</span></td>
            <td><span class="label">Tanggal Permintaan</span><span class="value">{{ $radiologyRequest->created_at?->format('d/m/Y H:i') }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="label">Catatan Klinis</span><span class="value">{{ $radiologyRequest->clinical_notes ?: '-' }}</span></td>
        </tr>
    </table>

    <table class="results">
        <thead>
            <tr>
                <th>Pemeriksaan</th>
                <th>Hasil / Temuan</th>
                <th>Kesimpulan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($radiologyRequest->items as $item)
            <tr>
                <td>
                    <div style="font-weight:bold">{{ $item->test_name }}</div>
                    <div style="color:#64748b;font-size:9px">{{ $item->reference_range ?? '' }}</div>
                </td>
                <td>{{ $item->result_findings ?: '-' }}</td>
                <td>{{ $item->result_impression ?: '-' }}</td>
                <td>
                    @if($item->result_status === 'abnormal') Abnormal
                    @elseif($item->result_status === 'normal') Normal
                    @else Belum Dibaca
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4">Tidak ada item pemeriksaan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dokumen ini dihasilkan oleh sistem HIS. Hasil radiologi sebaiknya dikonfirmasi oleh dokter spesialis radiologi.</div>
</body>
</html>