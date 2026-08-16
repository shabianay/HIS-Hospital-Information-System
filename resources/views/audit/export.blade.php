<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Audit Log</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #64748b; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        .detail { font-size: 9px; color: #475569; white-space: pre-wrap; word-break: break-all; max-width: 260px; }
    </style>
</head>
<body>
    <h1>Audit Log</h1>
    <div class="meta">Diekspor pada {{ $generatedAt }}</div>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Admin</th>
                <th>Modul</th>
                <th>ID</th>
                <th>Aksi</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                <td>{{ $log->user?->name ?? '(Sistem)' }}</td>
                <td>{{ class_basename($log->auditable_type) }}</td>
                <td>{{ $log->auditable_id }}</td>
                <td>{{ $log->action }}</td>
                <td class="detail">{{ json_encode($log->new_values ?: $log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center">Tidak ada log.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>