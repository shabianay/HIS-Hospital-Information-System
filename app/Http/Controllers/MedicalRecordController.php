<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Diagnosis;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', MedicalRecord::class);

        $records = MedicalRecord::with(['appointment.patient', 'appointment.doctor', 'appointment.poli'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('medical-records.index', compact('records'));
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        $medicalRecord->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'diagnoses',
            'prescriptions.medicine',
        ]);

        return view('medical-records.show', compact('medicalRecord'));
    }

    public function create($appointmentId)
    {
        $this->authorize('create', MedicalRecord::class);

        $appointment = Appointment::with(['patient', 'doctor', 'poli'])
            ->findOrFail($appointmentId);

        if ($appointment->medicalRecord) {
            return redirect()->route('medical-records.show', $appointment->medicalRecord)
                ->with('info', 'Rekam medis untuk kunjungan ini sudah ada.');
        }

        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();

        return view('medical-records.create', compact('appointment', 'medicines'));
    }

    public function store(Request $request, $appointmentId)
    {
        $this->authorize('create', MedicalRecord::class);

        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->medicalRecord) {
            return back()->with('error', 'Rekam medis untuk kunjungan ini sudah ada.');
        }

        $validated = $request->validate([
            'subjective' => 'required|string',
            'objective' => 'required|string',
            'assessment' => 'required|string',
            'plan' => 'required|string',
            'chief_complaint' => 'nullable|string',
            'blood_pressure_systolic' => 'nullable|numeric|min:0|max:300',
            'blood_pressure_diastolic' => 'nullable|numeric|min:0|max:300',
            'heart_rate' => 'nullable|numeric|min:0|max:300',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'allergy_notes' => 'nullable|string',
            'diagnoses' => 'required|array|min:1',
            'diagnoses.*.icd_code' => 'required|string|max:20',
            'diagnoses.*.description' => 'required|string|max:255',
            'diagnoses.*.is_primary' => 'boolean',
            'prescriptions' => 'nullable|array',
            'prescriptions.*.medicine_id' => 'required|exists:medicines,id',
            'prescriptions.*.quantity' => 'required|integer|min:1',
            'prescriptions.*.dosage' => 'required|string|max:255',
            'prescriptions.*.frequency' => 'required|string|max:255',
            'prescriptions.*.duration' => 'nullable|string|max:255',
            'prescriptions.*.instructions' => 'nullable|string|max:500',
        ]);

        $medicalRecord = DB::transaction(function () use ($validated, $appointment) {
            $record = MedicalRecord::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'subjective' => $validated['subjective'],
                'objective' => $validated['objective'],
                'assessment' => $validated['assessment'],
                'plan' => $validated['plan'],
                'chief_complaint' => $validated['chief_complaint'] ?? null,
                'blood_pressure_systolic' => $validated['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $validated['blood_pressure_diastolic'] ?? null,
                'heart_rate' => $validated['heart_rate'] ?? null,
                'temperature' => $validated['temperature'] ?? null,
                'weight' => $validated['weight'] ?? null,
                'height' => $validated['height'] ?? null,
                'allergy_notes' => $validated['allergy_notes'] ?? null,
                'status' => 'draft',
            ]);

            foreach ($validated['diagnoses'] as $diagnosis) {
                Diagnosis::create([
                    'medical_record_id' => $record->id,
                    'icd_code' => $diagnosis['icd_code'],
                    'description' => $diagnosis['description'],
                    'is_primary' => $diagnosis['is_primary'] ?? true,
                ]);
            }

            foreach ($validated['prescriptions'] ?? [] as $item) {
                Prescription::create([
                    'medical_record_id' => $record->id,
                    'medicine_id' => $item['medicine_id'],
                    'quantity' => $item['quantity'],
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'duration' => $item['duration'] ?? null,
                    'instructions' => $item['instructions'] ?? null,
                    'is_dispensed' => false,
                ]);
            }

            return $record;
        });

        return redirect()->route('medical-records.show', $medicalRecord)
            ->with('success', 'Rekam medis berhasil dibuat.');
    }

    public function edit(MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);

        if ($medicalRecord->status !== 'draft') {
            return back()->with('error', 'Hanya rekam medis berstatus draft yang dapat diedit.');
        }

        $medicalRecord->load([
            'appointment',
            'diagnoses',
            'prescriptions.medicine',
        ]);

        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();

        return view('medical-records.edit', compact('medicalRecord', 'medicines'));
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $this->authorize('update', $medicalRecord);

        if ($medicalRecord->status !== 'draft') {
            return back()->with('error', 'Hanya rekam medis berstatus draft yang dapat diperbarui.');
        }

        $validated = $request->validate([
            'subjective' => 'required|string',
            'objective' => 'required|string',
            'assessment' => 'required|string',
            'plan' => 'required|string',
            'chief_complaint' => 'nullable|string',
            'blood_pressure_systolic' => 'nullable|numeric|min:0|max:300',
            'blood_pressure_diastolic' => 'nullable|numeric|min:0|max:300',
            'heart_rate' => 'nullable|numeric|min:0|max:300',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'weight' => 'nullable|numeric|min:0|max:500',
            'height' => 'nullable|numeric|min:0|max:300',
            'allergy_notes' => 'nullable|string',
            'status' => 'sometimes|in:draft,finalized',
        ]);

        $medicalRecord->update($validated);

        return redirect()->route('medical-records.show', $medicalRecord)
            ->with('success', 'Rekam medis berhasil diperbarui.');
    }

    public function history($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $this->authorize('view', $patient);

        $records = MedicalRecord::with(['appointment.doctor', 'appointment.poli', 'diagnoses'])
            ->where('patient_id', $patientId)
            ->latest()
            ->paginate(15);

        return view('medical-records.history', compact('patient', 'records'));
    }

    public function exportPdf(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        $medicalRecord->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'diagnoses',
            'prescriptions.medicine',
        ]);

        $pdf = Pdf::loadView('medical-records.pdf', [
            'medicalRecord' => $medicalRecord,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('rekam-medis-' . str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }
}
