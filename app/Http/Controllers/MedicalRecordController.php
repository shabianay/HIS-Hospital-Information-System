<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Diagnosis;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\PrescriptionCreated;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    public function indexCsv()
    {
        $this->authorize('viewAny', MedicalRecord::class);

        $records = MedicalRecord::with(['appointment.patient', 'appointment.doctor', 'appointment.poli'])
            ->latest()
            ->get();

        $filename = 'rekam-medis-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA REKAM MEDIS RUMAH SAKIT HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Tanggal', 'No. RM', 'Pasien', 'Poli', 'Dokter', 'Keluhan', 'Diagnosa', 'Jumlah Obat']);
            foreach ($records as $record) {
                $diagnoses = $record->diagnoses->pluck('description')->implode('; ');
                fputcsv($handle, [
                    $record->appointment?->appointment_date?->format('d/m/Y'),
                    $record->appointment?->patient?->rm_number ?? '-',
                    $record->appointment?->patient?->name ?? '-',
                    $record->appointment?->poli?->name ?? '-',
                    $record->appointment?->doctor?->name ?? '-',
                    $record->complaints ?? '',
                    $diagnoses,
                    $record->prescriptions()->count(),
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL REKAM MEDIS', $records->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        $medicalRecord->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'appointment.labRequests.items',
            'diagnoses',
            'prescriptions.medicine',
        ]);

        return view('medical-records.show', compact('medicalRecord'));
    }

    public function create($appointmentId)
    {
        $this->authorize('create', MedicalRecord::class);

        $appointment = Appointment::with(['patient', 'doctor', 'poli', 'vitalSign'])
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
                $prescription = Prescription::create([
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

        $this->notifyPharmacistsOfPendingPrescriptions($medicalRecord);

        Cache::forget('dashboard.' . now()->format('Y-m-d'));

        return redirect()->route('medical-records.show', $medicalRecord)
            ->with('success', 'Rekam medis berhasil dibuat.');
    }

    private function notifyPharmacistsOfPendingPrescriptions(MedicalRecord $record): void
    {
        $pending = $record->prescriptions()->where('is_dispensed', false)->get();
        if ($pending->isEmpty()) {
            return;
        }

        $pharmacists = User::role('pharmacist')->get();
        if ($pharmacists->isEmpty()) {
            return;
        }

        foreach ($pending as $prescription) {
            foreach ($pharmacists as $pharmacist) {
                $pharmacist->notify(new PrescriptionCreated($prescription));
            }
        }
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

        $records = MedicalRecord::with(['appointment.doctor', 'appointment.poli', 'appointment.labRequests', 'diagnoses'])
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
            'appointment.labRequests.items',
            'diagnoses',
            'prescriptions.medicine',
        ]);

        $pdf = Pdf::loadView('medical-records.pdf', [
            'medicalRecord' => $medicalRecord,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('rekam-medis-' . str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function sickNotePdf(MedicalRecord $medicalRecord, Request $request)
    {
        $this->authorize('view', $medicalRecord);

        $days = max(1, (int) $request->get('days', 1));

        $medicalRecord->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'diagnoses',
        ]);

        $pdf = Pdf::loadView('medical-records.sick-note', [
            'medicalRecord' => $medicalRecord,
            'days' => $days,
            'daysWord' => $this->numberToIndonesianWords($days),
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('surat-keterangan-sakit-' . str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function prescriptionPdf(MedicalRecord $medicalRecord)
    {
        $this->authorize('view', $medicalRecord);

        $medicalRecord->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'prescriptions.medicine',
        ]);

        if ($medicalRecord->prescriptions->isEmpty()) {
            return back()->with('error', 'Rekam medis ini tidak memiliki resep obat.');
        }

        $pdf = Pdf::loadView('medical-records.prescription', [
            'medicalRecord' => $medicalRecord,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('resep-' . str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }

    public function referralPdf(MedicalRecord $medicalRecord, Request $request)
    {
        $this->authorize('view', $medicalRecord);

        $destination = trim((string) $request->get('destination', ''));
        if ($destination === '') {
            return back()->with('error', 'Nama fasilitas tujuan rujukan wajib diisi.');
        }

        $medicalRecord->load([
            'appointment.patient',
            'appointment.doctor',
            'appointment.poli',
            'diagnoses',
            'prescriptions.medicine',
            'appointment.labRequests.items',
        ]);

        $pdf = Pdf::loadView('medical-records.referral', [
            'medicalRecord' => $medicalRecord,
            'destination' => $destination,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('surat-rujukan-' . str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) . '.pdf');
    }

    private function numberToIndonesianWords(int $number): string
    {
        $units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];
        $teens = ['sepuluh', 'sebelas', 'dua belas', 'tiga belas', 'empat belas', 'lima belas', 'enam belas', 'tujuh belas', 'delapan belas', 'sembilan belas'];
        $tens = ['', '', 'dua puluh', 'tiga puluh', 'empat puluh', 'lima puluh', 'enam puluh', 'tujuh puluh', 'delapan puluh', 'sembilan puluh'];

        if ($number < 10) {
            return $units[$number];
        }
        if ($number < 20) {
            return $teens[$number - 10];
        }
        if ($number < 100) {
            return trim($tens[intdiv($number, 10)] . ($number % 10 ? ' ' . $units[$number % 10] : ''));
        }
        if ($number < 200) {
            return 'seratus' . ($number % 100 ? ' ' . $this->numberToIndonesianWords($number % 100) : '');
        }
        if ($number < 1000) {
            return $units[intdiv($number, 100)] . ' ratus' . ($number % 100 ? ' ' . $this->numberToIndonesianWords($number % 100) : '');
        }

        return (string) $number;
    }

    public function historyPdf($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $this->authorize('view', $patient);

        $records = MedicalRecord::with([
            'appointment.doctor',
            'appointment.poli',
            'appointment.labRequests.items',
            'diagnoses',
            'prescriptions.medicine',
        ])
            ->where('patient_id', $patientId)
            ->latest()
            ->get();

        $pdf = Pdf::loadView('medical-records.history-pdf', [
            'patient' => $patient,
            'records' => $records,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4');

        return $pdf->download('riwayat-medis-' . ($patient->rm_number ?: str_pad($patient->id, 4, '0', STR_PAD_LEFT)) . '.pdf');
    }
}
