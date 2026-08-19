<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\RadiologyRequest;
use App\Models\RadiologyRequestItem;
use App\Models\RadiologyTest;
use App\Models\User;
use App\Notifications\RadiologyRequestCreated;
use App\Notifications\RadiologyResultCompleted;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RadiologyController extends Controller
{
    // ─── Master tests ─────────────────────────────────────────────

    public function tests(Request $request)
    {
        $this->authorize('viewAny', RadiologyTest::class);

        $tests = RadiologyTest::orderBy('name')->paginate(20)->withQueryString();

        return view('radiology.tests', compact('tests'));
    }

    public function testsCsv()
    {
        $this->authorize('viewAny', RadiologyTest::class);

        $tests = RadiologyTest::orderBy('name')->get();

        $filename = 'master-tes-radiologi-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($tests) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['MASTER PEMERIKSAAN RADIOLOGI']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Nama', 'Kategori', 'Satuan', 'Keterangan', 'Harga', 'Status']);
            foreach ($tests as $test) {
                fputcsv($handle, [
                    $test->name,
                    $test->category ?? '-',
                    $test->unit ?? '-',
                    $test->reference_range ?? '-',
                    number_format((float) $test->price, 2),
                    $test->is_active ? 'Aktif' : 'Nonaktif',
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL PEMERIKSAAN', $tests->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function testsStore(Request $request)
    {
        $this->authorize('create', RadiologyTest::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'reference_range' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        RadiologyTest::create($validated + ['price' => (float) ($validated['price'] ?? 0)]);

        return redirect()->route('radiology.tests')->with('success', 'Pemeriksaan radiologi berhasil ditambahkan.');
    }

    public function testsUpdate(Request $request, RadiologyTest $radiologyTest)
    {
        $this->authorize('update', $radiologyTest);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'reference_range' => 'nullable|string|max:1000',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = (bool) $request->boolean('is_active');
        }

        $radiologyTest->update($validated + ['price' => (float) ($validated['price'] ?? $radiologyTest->price)]);

        return redirect()->route('radiology.tests')->with('success', 'Pemeriksaan radiologi berhasil diperbarui.');
    }

    public function testsDestroy(RadiologyTest $radiologyTest)
    {
        $this->authorize('delete', $radiologyTest);

        $radiologyTest->delete();

        return redirect()->route('radiology.tests')->with('success', 'Pemeriksaan radiologi berhasil dihapus.');
    }

    // ─── Requests ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', RadiologyRequest::class);

        $query = RadiologyRequest::with(['patient', 'doctor', 'appointment'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $requests = $query->paginate(15)->withQueryString();

        $summary = [
            'pending' => RadiologyRequest::where('status', 'pending')->count(),
            'in_progress' => RadiologyRequest::where('status', 'in_progress')->count(),
            'completed' => RadiologyRequest::where('status', 'completed')->count(),
            'urgent' => RadiologyRequest::where('status', '!=', 'completed')->where('is_urgent', true)->count(),
        ];

        return view('radiology.requests', compact('requests', 'summary'));
    }

    public function exportCsv(Request $request)
    {
        $this->authorize('viewAny', RadiologyRequest::class);

        $query = RadiologyRequest::with(['patient', 'doctor', 'appointment'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();

        $statusLabels = [
            'pending' => 'Menunggu',
            'in_progress' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        $filename = 'permintaan-radiologi-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($requests, $statusLabels) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['PERMINTAAN RADIOLOGI']);
            fputcsv($handle, ['No. Permintaan', 'Tanggal', 'Pasien', 'Dokter', 'Urgent', 'Status']);

            foreach ($requests as $req) {
                fputcsv($handle, [
                    $req->id,
                    $req->created_at?->format('d/m/Y H:i'),
                    $req->patient?->name ?? '-',
                    $req->doctor?->name ?? '-',
                    $req->is_urgent ? 'Ya' : 'Tidak',
                    $statusLabels[$req->status] ?? $req->status,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create(Request $request)
    {
        $this->authorize('create', RadiologyRequest::class);

        $appointment = null;
        $medicalRecord = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor', 'poli'])->find($request->appointment_id);
        }

        if ($request->filled('medical_record_id')) {
            $medicalRecord = MedicalRecord::with(['appointment.patient'])->find($request->medical_record_id);
        }

        $tests = RadiologyTest::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $categories = $tests->groupBy('category');

        return view('radiology.create', compact('tests', 'appointment', 'medicalRecord', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', RadiologyRequest::class);

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'patient_id' => 'required|exists:patients,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'is_urgent' => 'nullable|boolean',
            'clinical_notes' => 'nullable|string|max:2000',
            'radiology_test_ids' => 'required|array|min:1',
            'radiology_test_ids.*' => 'required|exists:radiology_tests,id',
        ]);

        $tests = RadiologyTest::whereIn('id', $validated['radiology_test_ids'])->get();
        $appointment = Appointment::find($validated['appointment_id']);

        $requestRecord = DB::transaction(function () use ($validated, $tests, $appointment) {
            $requestRecord = RadiologyRequest::create([
                'appointment_id' => $validated['appointment_id'],
                'patient_id' => $validated['patient_id'],
                'medical_record_id' => $validated['medical_record_id'] ?? null,
                'doctor_id' => $appointment?->doctor_id,
                'created_by' => Auth::id(),
                'is_urgent' => (bool) ($validated['is_urgent'] ?? false),
                'status' => 'pending',
                'clinical_notes' => $validated['clinical_notes'] ?? null,
            ]);

            foreach ($tests as $test) {
                RadiologyRequestItem::create([
                    'radiology_request_id' => $requestRecord->id,
                    'radiology_test_id' => $test->id,
                    'test_name' => $test->name,
                    'reference_range' => $test->reference_range,
                    'price' => $test->price,
                    'result_status' => 'pending',
                ]);
            }

            return $requestRecord;
        });

        $users = User::role('admin')->get()->filter(fn ($u) => $u->id !== Auth::id());
        if ($users->isNotEmpty()) {
            foreach ($users as $user) {
                $user->notify(new RadiologyRequestCreated($requestRecord));
            }
        }

        $this->forgetDashboardCache();

        return redirect()->route('radiology.requests.show', $requestRecord)
            ->with('success', 'Permintaan radiologi berhasil dibuat.');
    }

    public function show(RadiologyRequest $radiologyRequest)
    {
        $this->authorize('view', $radiologyRequest);

        $radiologyRequest->load(['patient', 'doctor', 'appointment.poli', 'items.test', 'createdBy']);

        return view('radiology.show', compact('radiologyRequest'));
    }

    public function processStore(Request $request, RadiologyRequest $radiologyRequest)
    {
        $this->authorize('update', $radiologyRequest);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.result_findings' => 'nullable|string|max:2000',
            'items.*.result_impression' => 'nullable|string|max:2000',
            'items.*.result_status' => 'nullable|in:pending,normal,abnormal',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        DB::transaction(function () use ($radiologyRequest, $validated) {
            foreach ($validated['items'] as $itemId => $data) {
                $item = $radiologyRequest->items()->find($itemId);
                if (! $item) {
                    continue;
                }

                $item->result_findings = $data['result_findings'] ?? null;
                $item->result_impression = $data['result_impression'] ?? null;
                $item->result_status = $data['result_status'] ?? 'pending';
                $item->save();
            }

            $status = $validated['status'] ?? $radiologyRequest->status;
            $radiologyRequest->status = $status;
            $radiologyRequest->completed_at = $status === 'completed' ? now() : null;
            $radiologyRequest->save();
        });

        if ($radiologyRequest->status === 'completed') {
            $recipients = collect();

            if ($radiologyRequest->created_by) {
                $creator = User::find($radiologyRequest->created_by);
                if ($creator && $creator->id !== Auth::id()) {
                    $recipients->push($creator);
                }
            }

            $doctor = $radiologyRequest->doctor;
            if ($doctor?->user_id) {
                $recipients->push($doctor->user);
            }

            foreach (User::role('admin')->get() as $admin) {
                if ($admin->id !== Auth::id()) {
                    $recipients->push($admin);
                }
            }

            foreach (User::role('cashier')->get() as $cashier) {
                if ($cashier->id !== Auth::id()) {
                    $recipients->push($cashier);
                }
            }

            foreach ($recipients->unique('id') as $recipient) {
                $recipient->notify(new RadiologyResultCompleted($radiologyRequest));
            }
        }

        $this->forgetDashboardCache();

        return redirect()->route('radiology.requests.show', $radiologyRequest)
            ->with('success', 'Hasil radiologi berhasil disimpan.');
    }

    private function forgetDashboardCache(): void
    {
        Cache::forget('dashboard.' . now()->format('Y-m-d'));
    }

    public function destroy(RadiologyRequest $radiologyRequest)
    {
        $this->authorize('delete', $radiologyRequest);

        $radiologyRequest->delete();

        return redirect()->route('radiology.requests')->with('success', 'Permintaan radiologi dihapus.');
    }

    public function exportPdf(RadiologyRequest $radiologyRequest)
    {
        $this->authorize('view', $radiologyRequest);

        $radiologyRequest->load(['patient', 'doctor', 'appointment.poli', 'items', 'createdBy']);

        $pdf = Pdf::loadView('radiology.pdf', [
            'radiologyRequest' => $radiologyRequest,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('hasil-radiologi-' . str_pad($radiologyRequest->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }
}