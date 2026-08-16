<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = $this->filteredQuery($request)->paginate(25)->withQueryString();

        return view('audit.index', compact('logs'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = $this->filteredQuery($request)->limit(10000)->get();

        $filename = 'audit-log-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Waktu', 'Admin', 'Modul', 'ID', 'Aksi', 'Detail']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at?->format('d/m/Y H:i:s'),
                    $log->user?->name ?? '(Sistem)',
                    class_basename($log->auditable_type),
                    $log->auditable_id,
                    $log->action,
                    json_encode($log->new_values ?: $log->old_values, JSON_UNESCAPED_UNICODE),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $logs = $this->filteredQuery($request)->limit(2000)->get();

        $pdf = Pdf::loadView('audit.export', [
            'logs' => $logs,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('audit-log-' . now()->format('Y-m-d-His') . '.pdf');
    }

    private function filteredQuery(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return $query;
    }
}