<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Notifications\LabRequestCreated;
use App\Notifications\LabResultCompleted;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    // ─── Master tests ─────────────────────────────────────────────

    public function tests(Request $request)
    {
        $this->authorize('viewAny', LabTest::class);

        $tests = LabTest::orderBy('name')->paginate(20)->withQueryString();

        return view('lab.tests', compact('tests'));
    }

    public function testsStore(Request $request)
    {
        $this->authorize('create', LabTest::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'reference_range' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        LabTest::create($validated + ['price' => (float) ($validated['price'] ?? 0)]);

        return redirect()->route('lab.tests')->with('success', 'Tes laboratorium berhasil ditambahkan.');
    }

    public function testsUpdate(Request $request, LabTest $labTest)
    {
        $this->authorize('update', $labTest);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
            'reference_range' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = (bool) $request->boolean('is_active');
        }

        $labTest->update($validated + ['price' => (float) ($validated['price'] ?? $labTest->price)]);

        return redirect()->route('lab.tests')->with('success', 'Tes laboratorium berhasil diperbarui.');
    }

    public function testsDestroy(LabTest $labTest)
    {
        $this->authorize('delete', $labTest);

        $labTest->delete();

        return redirect()->route('lab.tests')->with('success', 'Tes laboratorium berhasil dihapus.');
    }

    // ─── Requests ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('viewAny', LabRequest::class);

        $query = LabRequest::with(['patient', 'doctor', 'appointment'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $requests = $query->paginate(15)->withQueryString();

        return view('lab.requests', compact('requests'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', LabRequest::class);

        $appointment = null;
        $medicalRecord = null;

        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor', 'poli'])->find($request->appointment_id);
        }

        if ($request->filled('medical_record_id')) {
            $medicalRecord = MedicalRecord::with(['appointment.patient'])->find($request->medical_record_id);
        }

        $tests = LabTest::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $categories = $tests->groupBy('category');

        return view('lab.create', compact('tests', 'appointment', 'medicalRecord', 'categories'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', LabRequest::class);

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'patient_id' => 'required|exists:patients,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'is_urgent' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'lab_test_ids' => 'required|array|min:1',
            'lab_test_ids.*' => 'required|exists:lab_tests,id',
        ]);

        $labTests = LabTest::whereIn('id', $validated['lab_test_ids'])->get();
        $appointment = Appointment::find($validated['appointment_id']);

        $labRequest = DB::transaction(function () use ($validated, $labTests, $appointment) {
            $requestRecord = LabRequest::create([
                'appointment_id' => $validated['appointment_id'],
                'patient_id' => $validated['patient_id'],
                'medical_record_id' => $validated['medical_record_id'] ?? null,
                'doctor_id' => $appointment?->doctor_id,
                'created_by' => Auth::id(),
                'is_urgent' => (bool) ($validated['is_urgent'] ?? false),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($labTests as $test) {
                LabRequestItem::create([
                    'lab_request_id' => $requestRecord->id,
                    'lab_test_id' => $test->id,
                    'test_name' => $test->name,
                    'unit' => $test->unit,
                    'reference_range' => $test->reference_range,
                    'price' => $test->price,
                    'result_status' => 'pending',
                ]);
            }

            return $requestRecord;
        });

        $labTechs = User::role('lab_tech')->get();
        if ($labTechs->isNotEmpty()) {
            foreach ($labTechs as $labTech) {
                $labTech->notify(new LabRequestCreated($labRequest));
            }
        }

        return redirect()->route('lab.requests.show', $labRequest)
            ->with('success', 'Permintaan laboratorium berhasil dibuat.');
    }

    public function show(LabRequest $labRequest)
    {
        $this->authorize('view', $labRequest);

        $labRequest->load(['patient', 'doctor', 'appointment.poli', 'items.labTest', 'createdBy']);

        return view('lab.show', compact('labRequest'));
    }

    public function processStore(Request $request, LabRequest $labRequest)
    {
        $this->authorize('update', $labRequest);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.result_value' => 'nullable|string|max:500',
            'items.*.result_status' => 'nullable|in:pending,normal,abnormal',
            'items.*.result_notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
        ]);

        DB::transaction(function () use ($labRequest, $validated) {
            foreach ($validated['items'] as $itemId => $data) {
                $item = $labRequest->items()->find($itemId);
                if (! $item) {
                    continue;
                }

                $item->result_value = $data['result_value'] ?? null;
                $item->result_status = $data['result_status'] ?? 'pending';
                $item->result_notes = $data['result_notes'] ?? null;
                $item->save();
            }

            $status = $validated['status'] ?? $labRequest->status;
            $labRequest->status = $status;
            $labRequest->completed_at = $status === 'completed' ? now() : null;
            $labRequest->save();
        });

        if ($labRequest->status === 'completed') {
            $recipients = collect();

            if ($labRequest->created_by) {
                $creator = User::find($labRequest->created_by);
                if ($creator && $creator->id !== Auth::id()) {
                    $recipients->push($creator);
                }
            }

            $doctor = $labRequest->doctor;
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
                $recipient->notify(new LabResultCompleted($labRequest));
            }
        }

        return redirect()->route('lab.requests.show', $labRequest)
            ->with('success', 'Hasil laboratorium berhasil disimpan.');
    }

    public function destroy(LabRequest $labRequest)
    {
        $this->authorize('delete', $labRequest);

        $labRequest->delete();

        return redirect()->route('lab.requests')->with('success', 'Permintaan laboratorium dihapus.');
    }

    public function exportPdf(LabRequest $labRequest)
    {
        $this->authorize('view', $labRequest);

        $labRequest->load(['patient', 'doctor', 'appointment.poli', 'items', 'createdBy']);

        $pdf = Pdf::loadView('lab.pdf', [
            'labRequest' => $labRequest,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('hasil-lab-' . str_pad($labRequest->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }
}