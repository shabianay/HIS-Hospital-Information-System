<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\LabRequest;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Schedule;
use App\Models\ShiftReconciliation;
use App\Models\Tariff;
use App\Models\User;
use App\Notifications\AppointmentCancelled;
use App\Notifications\AppointmentCreated;
use App\Notifications\AppointmentReminder;
use App\Notifications\PatientCalled;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    private function seedNurse(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'perawat@his.local')->firstOrFail();
    }

    public function test_admin_can_open_every_module_index_page(): void
    {
        $user = $this->seedAdmin();

        $routes = [
            'dashboard',
            'appointments.index',
            'patients.index',
            'doctors.index',
            'polis.index',
            'schedules.index',
            'schedules.board',
            'medical-records.index',
            'medicines.index',
            'medicines.stock',
            'billings.index',
            'billings.daily-report',
            'tariffs.index',
            'users.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_open_every_module_create_page(): void
    {
        $user = $this->seedAdmin();

        $routes = [
            'appointments.create',
            'patients.create',
            'doctors.create',
            'polis.create',
            'schedules.create',
            'medicines.create',
            'tariffs.create',
            'users.create',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_open_queue_display(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('queue.display'))->assertOk();
    }

    public function test_admin_can_view_patient_detail_and_history(): void
    {
        $user = $this->seedAdmin();

        $patient = Patient::first();

        $this->actingAs($user)->get(route('patients.show', $patient))->assertOk();
        $this->actingAs($user)->get(route('patients.show', $patient))->assertSee('Saldo Tagihan Belum Lunas');
        $this->actingAs($user)->get(route('patients.medical-history', $patient))->assertOk();
        $this->actingAs($user)->get(route('patients.medical-history.pdf', $patient))->assertOk();
        $this->actingAs($user)->get(route('patients.card', $patient))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_open_appointment_and_create_medical_record_screen(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $this->actingAs($user)->get(route('appointments.show', $appointment))->assertOk();

        if (! $appointment->medicalRecord) {
            $this->actingAs($user)->get(route('medical-records.create', $appointment))->assertOk();
        } else {
            $this->actingAs($user)->get(route('medical-records.show', $appointment->medicalRecord))->assertOk();
        }
    }

    public function test_admin_can_print_appointment_queue_ticket(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $this->actingAs($user)->get(route('appointments.ticket', $appointment))
            ->assertOk()
            ->assertSee($appointment->queue_number)
            ->assertSee('Cetak Tiket');
    }

    public function test_creating_appointment_notifies_doctor(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $schedule = Schedule::find($appointment->schedule_id);

        $schedule->update(['daily_quota' => 100]);

        $this->actingAs($user)->post(route('appointments.store'), [
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'poli_id' => $appointment->poli_id,
            'schedule_id' => $appointment->schedule_id,
            'appointment_date' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $doctorUser = Doctor::find($appointment->doctor_id)->user;

        if ($doctorUser) {
            $this->assertGreaterThan(0, $doctorUser->notifications()->where('type', AppointmentCreated::class)->count());
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_complete_emr_pharmacy_and_billing_flow(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::first();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Demam sejak 2 hari, disertai batuk dan pilek.',
            'objective' => 'Suhu 38,2 C, tonsil tampak hiperemis.',
            'assessment' => 'Infeksi saluran pernapasan atas.',
            'plan' => 'Pemberian obat simptomatik dan observasi.',
            'chief_complaint' => 'Demam dan batuk',
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'heart_rate' => 88,
            'temperature' => 38.2,
            'weight' => 60,
            'height' => 170,
            'allergy_notes' => null,
            'diagnoses' => [
                [
                    'icd_code' => 'J00',
                    'description' => 'Acute nasopharyngitis [common cold]',
                    'is_primary' => 1,
                ],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 10,
                    'dosage' => '3x1',
                    'frequency' => 'Sesudah makan',
                    'duration' => '5 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $record = MedicalRecord::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($record);
        $this->actingAs($user)->get(route('medical-records.show', $record))->assertOk();

        $prescription = $record->prescriptions()->first();
        $this->actingAs($user)->post(route('prescriptions.dispense', $prescription))->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('billings.create', $appointment))->assertOk();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee,
            'medicine_fee' => $prescription->quantity * (float) $prescription->medicine->sell_price,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $this->assertNotNull($billing);
        $this->assertGreaterThan(0, (float) $billing->total_amount);

        $this->actingAs($user)->patch(route('billings.payment', $billing), [
            'payments' => [
                ['method' => 'cash', 'amount' => $billing->total_amount],
            ],
        ])->assertSessionHasNoErrors();

        $billing->refresh();
        $this->assertEquals('paid', $billing->status);

        $this->actingAs($user)->get(route('billings.receipt', $billing))->assertOk();
    }

    public function test_billing_payment_can_be_split_across_multiple_methods(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::first();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee ?? 50000,
            'medicine_fee' => 0,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $total = (float) $billing->total_amount;
        $this->assertGreaterThan(0, $total);

        $cash = round($total / 2, 2);
        $qris = $total - $cash;

        $this->actingAs($user)->patch(route('billings.payment', $billing), [
            'payments' => [
                ['method' => 'cash', 'amount' => $cash],
                ['method' => 'qris', 'amount' => $qris],
            ],
        ])->assertSessionHasNoErrors();

        $billing->refresh();
        $this->assertEquals('paid', $billing->status);
        $this->assertEquals($total, (float) $billing->paid_amount);
        $this->assertDatabaseHas('billing_payments', ['billing_id' => $billing->id, 'payment_method' => 'cash', 'amount' => $cash]);
        $this->assertDatabaseHas('billing_payments', ['billing_id' => $billing->id, 'payment_method' => 'qris', 'amount' => $qris]);

        $this->actingAs($user)->get(route('billings.show', $billing))
            ->assertOk()
            ->assertSee('Tunai (Cash)')
            ->assertSee('QRIS / E-Wallet');
    }

    public function test_billing_payment_rejects_overpayment(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::first();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee ?? 50000,
            'medicine_fee' => 0,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $total = (float) $billing->total_amount;

        $this->actingAs($user)->patch(route('billings.payment', $billing), [
            'payments' => [
                ['method' => 'cash', 'amount' => $total + 1000],
            ],
        ])->assertSessionHasErrors('payments');

        $billing->refresh();
        $this->assertEquals('unpaid', $billing->status);
    }

    public function test_cash_reconciliation_per_shift_records_difference(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::first();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee ?? 50000,
            'medicine_fee' => 0,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $total = (float) $billing->total_amount;

        $this->actingAs($user)->patch(route('billings.payment', $billing), [
            'payments' => [
                ['method' => 'cash', 'amount' => $total],
            ],
        ])->assertSessionHasNoErrors();

        $payment = $billing->payments()->where('payment_method', 'cash')->first();
        $this->assertNotNull($payment);

        $shift = $this->shiftForHour((int) $payment->created_at->format('H'));
        $today = now()->format('Y-m-d');

        $this->actingAs($user)->get(route('billings.reconciliation', ['date' => $today]))
            ->assertOk()
            ->assertSee('Rekonsiliasi Kas per Shift');

        $this->actingAs($user)->post(route('billings.reconciliation.store', $shift), [
            'date' => $today,
            'counted_cash' => $total,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('shift_reconciliations', [
            'shift' => $shift,
            'expected_cash' => $total,
            'counted_cash' => $total,
            'difference' => 0,
            'transaction_count' => 1,
        ]);

        $reconciliation = ShiftReconciliation::where('shift', $shift)->first();
        $this->assertNotNull($reconciliation);
        $this->assertEquals($today, $reconciliation->reconciliation_date->format('Y-m-d'));

        $this->actingAs($user)->get(route('billings.reconciliation', ['date' => $today]))
            ->assertOk()
            ->assertSee('Selesai Rekonsiliasi');
    }

    public function test_cash_reconciliation_marks_mismatch(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::first();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee ?? 50000,
            'medicine_fee' => 0,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $total = (float) $billing->total_amount;

        $this->actingAs($user)->patch(route('billings.payment', $billing), [
            'payments' => [
                ['method' => 'cash', 'amount' => $total],
            ],
        ])->assertSessionHasNoErrors();

        $payment = $billing->payments()->where('payment_method', 'cash')->first();
        $shift = $this->shiftForHour((int) $payment->created_at->format('H'));
        $today = now()->format('Y-m-d');

        $this->actingAs($user)->post(route('billings.reconciliation.store', $shift), [
            'date' => $today,
            'counted_cash' => $total - 5000,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('shift_reconciliations', [
            'shift' => $shift,
            'expected_cash' => $total,
            'difference' => -5000,
        ]);

        $this->actingAs($user)->get(route('billings.reconciliation', ['date' => $today]))
            ->assertOk()
            ->assertSee('kurang');
    }

    private function shiftForHour(int $hour): string
    {
        if ($hour >= 7 && $hour < 14) {
            return 'pagi';
        }
        if ($hour >= 14 && $hour < 21) {
            return 'siang';
        }

        return 'malam';
    }

    public function test_daily_quota_is_enforced_atomically(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $scheduleId = $appointment->schedule_id;

        $existing = Appointment::where('schedule_id', $scheduleId)
            ->whereDate('appointment_date', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->count();

        $schedule = Schedule::find($scheduleId);
        $schedule->update(['daily_quota' => $existing + 1]);

        $patients = Patient::orderBy('id')->get();
        $payload = [
            'patient_id' => $patients->first()->id,
            'doctor_id' => $appointment->doctor_id,
            'poli_id' => $appointment->poli_id,
            'schedule_id' => $scheduleId,
            'appointment_date' => now()->toDateString(),
        ];

        $this->actingAs($user)->post(route('appointments.store'), $payload)->assertSessionHasNoErrors();

        $payload['patient_id'] = $patients->skip(1)->first()->id;

        $response = $this->actingAs($user)->post(route('appointments.store'), $payload);
        $response->assertSessionHasErrors('schedule_id');
    }

    public function test_non_admin_user_with_view_dashboard_permission_can_open_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();
        $this->assertTrue($registration->hasPermissionTo('view-dashboard'));
        $this->assertFalse($registration->hasPermissionTo('manage-users'));

        $this->actingAs($registration)->get(route('dashboard'))->assertOk();
    }

    public function test_sidebar_menu_matches_role_permissions(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();
        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $nurse = User::where('email', 'perawat@his.local')->firstOrFail();
        $doctor = User::where('email', 'dokter@his.local')->firstOrFail();
        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();
        $labTech = User::where('email', 'lab@his.local')->firstOrFail();

        $regResponse = $this->actingAs($registration)->get(route('dashboard'));
        $regResponse->assertOk()
            ->assertSee('Antrian Hari Ini')
            ->assertSee('Display Antrian')
            ->assertDontSee('Kasir / Billing')
            ->assertDontSee('Display Lab')
            ->assertDontSee('Display Farmasi')
            ->assertDontSee('Laporan');

        $cashierResponse = $this->actingAs($cashier)->get(route('dashboard'));
        $cashierResponse->assertOk()
            ->assertSee('Kasir / Billing')
            ->assertDontSee('Antrian Hari Ini')
            ->assertDontSee('Laporan');

        $nurseResponse = $this->actingAs($nurse)->get(route('dashboard'));
        $nurseResponse->assertOk()
            ->assertSee('Antrian Hari Ini')
            ->assertSee('Rekam Medis')
            ->assertDontSee('Pasien Saya')
            ->assertDontSee('Laporan');

        $doctorResponse = $this->actingAs($doctor)->get(route('dashboard'));
        $doctorResponse->assertOk()
            ->assertSee('Rekam Medis')
            ->assertSee('Pasien Saya')
            ->assertDontSee('Antrian Hari Ini')
            ->assertDontSee('Laporan');

        $pharmacistResponse = $this->actingAs($pharmacist)->get(route('dashboard'));
        $pharmacistResponse->assertOk()
            ->assertSee('Farmasi')
            ->assertSee('Display Farmasi')
            ->assertDontSee('Display Antrian')
            ->assertDontSee('Display Lab')
            ->assertDontSee('Laporan');

        $labTechResponse = $this->actingAs($labTech)->get(route('dashboard'));
        $labTechResponse->assertOk()
            ->assertSee('Laboratorium')
            ->assertSee('Display Lab')
            ->assertDontSee('Display Antrian')
            ->assertDontSee('Display Farmasi')
            ->assertDontSee('Laporan');
    }

    public function test_admin_can_export_audit_csv_and_pdf(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('audit.export.csv'))->assertOk();
        $this->actingAs($user)->get(route('audit.export.pdf'))->assertOk();
    }

    public function test_master_data_changes_are_audited(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();
        $oldName = $medicine->name;

        $this->actingAs($user)->patch(route('medicines.update', $medicine), array_merge($medicine->toArray(), ['name' => $oldName.' (Revisi)']))
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Medicine::class,
            'auditable_id' => $medicine->id,
            'action' => 'updated',
        ]);
    }

    public function test_admin_can_export_daily_report_pdf(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('billings.daily-report.pdf'))->assertOk();
        $this->actingAs($user)->get(route('billings.daily-report.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_admin_can_export_billing_receipt_pdf(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee,
            'medicine_fee' => 0,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $this->assertNotNull($billing);

        $this->actingAs($user)->get(route('billings.receipt.pdf', $billing))->assertOk();
    }

    public function test_export_routes_require_authorization(): void
    {
        $this->seed(DatabaseSeeder::class);

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('audit.export.csv'))->assertForbidden();
        $this->actingAs($user)->get(route('audit.export.pdf'))->assertForbidden();
        $this->actingAs($user)->get(route('billings.daily-report.pdf'))->assertForbidden();
    }

    public function test_admin_can_export_medical_record_pdf(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::first();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Sakit kepala dan mual.',
            'objective' => 'Tensi 130/90.',
            'assessment' => 'Hipertensi.',
            'plan' => 'Kontrol rutin.',
            'chief_complaint' => 'Sakit kepala',
            'diagnoses' => [
                ['icd_code' => 'I10', 'description' => 'Essential (primary) hypertension', 'is_primary' => 1],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 10,
                    'dosage' => '1x1',
                    'frequency' => 'Pagi hari',
                    'duration' => '10 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $record = MedicalRecord::where('appointment_id', $appointment->id)->firstOrFail();

        $this->actingAs($user)->get(route('medical-records.pdf', $record))->assertOk();
    }

    public function test_admin_can_export_lab_request_pdf(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek darah lengkap.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();

        $this->actingAs($user)->get(route('lab.requests.pdf', $labRequest))->assertOk();
    }

    public function test_queue_display_accessible_to_authenticated_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->actingAs($registration)->get(route('queue.display'))->assertOk();
        $this->actingAs($registration)->get(route('queue.display.json'))->assertOk();
    }

    public function test_authenticated_user_can_export_queue_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->actingAs($registration)->get(route('appointments.queue.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_authenticated_user_can_export_appointment_list_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->actingAs($registration)->get(route('appointments.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_pharmacist_can_view_reorder_recommendations(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();
        $medicine->update(['minimum_stock' => 9999]);

        $this->actingAs($user)->get(route('medicines.reorder'))
            ->assertOk()
            ->assertSee('Rekomendasi Pembelian Stok');
    }

    public function test_pharmacist_can_export_reorder_pdf(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();
        $medicine->update(['minimum_stock' => 9999]);

        $this->actingAs($user)->get(route('medicines.reorder.pdf'))->assertOk();
    }

    public function test_pharmacist_can_view_stock_card(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'CARD-001',
            'quantity' => 10,
            'expiry_date' => now()->addYear()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('medicines.stock-card', ['medicine_id' => $medicine->id]))
            ->assertOk()
            ->assertSee('Kartu Stok Obat')
            ->assertSee($medicine->name);
    }

    public function test_pharmacist_can_view_expiring_stock_report(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();
        MedicineStock::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'EXP-001',
            'quantity' => 5,
            'expiry_date' => now()->addDays(20)->toDateString(),
        ]);

        $this->actingAs($user)->get(route('medicines.expiring'))
            ->assertOk()
            ->assertSee('Stok Mendekati Kedaluwarsa')
            ->assertSee('EXP-001');
    }

    public function test_lab_tech_can_export_lab_requests_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();

        $this->actingAs($labTech)->get(route('lab.requests.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_lab_tech_can_export_lab_tests_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();

        $this->actingAs($labTech)->get(route('lab.tests.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_cancelling_appointment_notifies_doctor(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $doctorUser = Doctor::find($appointment->doctor_id)?->user;

        if (! $doctorUser) {
            $this->assertTrue(true);

            return;
        }

        $this->actingAs($user)->patch(route('appointments.status.update', $appointment), [
            'status' => 'cancelled',
        ])->assertSessionHasNoErrors();

        $this->assertGreaterThan(0, $doctorUser->notifications()->where('type', AppointmentCancelled::class)->count());
    }

    public function test_calling_patient_notifies_doctor(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $doctorUser = Doctor::find($appointment->doctor_id)?->user;

        if (! $doctorUser) {
            $this->assertTrue(true);

            return;
        }

        $this->actingAs($user)->patch(route('appointments.status.update', $appointment), [
            'status' => 'in_progress',
        ])->assertSessionHasNoErrors();

        $this->assertGreaterThan(0, $doctorUser->notifications()->where('type', PatientCalled::class)->count());
    }

    public function test_appointment_reminder_command_notifies_doctor(): void
    {
        $user = $this->seedAdmin();

        $tomorrow = now()->addDay()->toDateString();
        $schedule = Schedule::first();
        $patient = Patient::first();

        if (! $schedule || ! $patient) {
            $this->assertTrue(true);

            return;
        }

        $appointment = Appointment::create([
            'queue_number' => 'QUMU-'.str_replace('-', '', $tomorrow).'-001',
            'patient_id' => $patient->id,
            'doctor_id' => $schedule->doctor_id,
            'poli_id' => $schedule->poli_id,
            'schedule_id' => $schedule->id,
            'appointment_date' => $tomorrow,
            'status' => 'waiting',
            'consultation_fee' => $schedule->consultation_fee,
        ]);

        $doctorUser = $appointment->doctor?->user;

        if (! $doctorUser) {
            $this->assertTrue(true);

            return;
        }

        $this->artisan('appointments:remind')->assertSuccessful();

        $this->assertGreaterThan(0, $doctorUser->notifications()->where('type', AppointmentReminder::class)->count());
    }

    public function test_stock_digest_command_notifies_pharmacist(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();
        MedicineStock::where('medicine_id', $medicine->id)->update(['quantity' => 1]);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();
        $pharmacist->notifications()->delete();

        $this->artisan('stock:digest')->assertSuccessful();

        $this->assertGreaterThan(0, $pharmacist->notifications()->count());
    }

    public function test_nurse_can_input_vital_signs(): void
    {
        $user = $this->seedNurse();
        $appointment = Appointment::first();

        $this->actingAs($user)->get(route('appointments.queue'))->assertOk();

        $this->actingAs($user)->get(route('vital-signs.create', $appointment))->assertOk();

        $this->actingAs($user)->post(route('vital-signs.store', $appointment), [
            'temperature' => 36.5,
            'blood_pressure_systolic' => 120,
            'blood_pressure_diastolic' => 80,
            'heart_rate' => 70,
            'respiratory_rate' => 18,
            'weight' => 60.0,
            'height' => 170.0,
            'oxygen_saturation' => 98,
            'notes' => 'Pasien stabil.',
        ])->assertRedirect(route('appointments.queue'));

        $this->assertDatabaseHas('vital_signs', [
            'appointment_id' => $appointment->id,
            'temperature' => 36.5,
        ]);

        $admin = $this->seedAdmin();
        $this->actingAs($admin)->get(route('appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Tanda Vital Pasien')
            ->assertSee('36.5 °C');
    }

    public function test_emr_create_prefills_nurse_vital_signs(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::first();

        $this->actingAs($user)->post(route('vital-signs.store', $appointment), [
            'temperature' => 37.0,
            'blood_pressure_systolic' => 130,
            'blood_pressure_diastolic' => 85,
            'heart_rate' => 75,
            'respiratory_rate' => 16,
            'weight' => 65.0,
            'height' => 172.0,
            'oxygen_saturation' => 99,
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('medical-records.create', $appointment))
            ->assertOk()
            ->assertSee('Terisi dari perawat')
            ->assertSee('value="130"', false)
            ->assertSee('value="75"', false);
    }

    public function test_patient_allergies_and_chronic_conditions_are_saved_and_displayed(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::first();

        $this->actingAs($user)->put(route('patients.update', $patient), [
            'name' => $patient->name,
            'nik' => $patient->nik,
            'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
            'gender' => $patient->gender,
            'phone_number' => $patient->phone_number,
            'address' => $patient->address,
            'insurance_provider' => $patient->insurance_provider,
            'insurance_number' => $patient->insurance_number,
            'allergies' => 'Penisilin, Parasetamol',
            'chronic_conditions' => 'Hipertensi, Diabetes Melitus',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'allergies' => 'Penisilin, Parasetamol',
            'chronic_conditions' => 'Hipertensi, Diabetes Melitus',
        ]);

        $this->actingAs($user)->get(route('patients.show', $patient))
            ->assertOk()
            ->assertSee('Riwayat Alergi')
            ->assertSee('Penyakit Kronis');
    }

    public function test_public_queue_lookup_shows_patient_position(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $this->get(route('queue.lookup'))->assertOk()->assertSee('Cek Status Antrian');

        $this->post(route('queue.lookup.search'), [
            'queue_number' => $appointment->queue_number,
        ])->assertSessionHasNoErrors()->assertSee($appointment->patient->name);

        $this->post(route('queue.lookup.search'), [
            'queue_number' => 'Q-NOT-FOUND',
        ])->assertSessionHas('error');
    }

    public function test_lab_queue_display_accessible_to_authenticated_users(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek lab.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('queue.display.lab'))->assertOk();
        $this->actingAs($user)->get(route('queue.display.lab.json'))
            ->assertOk()
            ->assertJson(['total' => 1]);
    }

    public function test_pharmacy_queue_display_accessible_to_authenticated_users(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'chief_complaint' => 'Demam',
            'subjective' => 'Demam 3 hari',
            'objective' => 'Suhu 38C',
            'assessment' => 'Dengue',
            'plan' => 'Istirahat',
            'diagnoses' => [
                [
                    'icd_code' => 'A90',
                    'description' => 'Dengue',
                    'is_primary' => true,
                ],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 10,
                    'dosage' => '3x1',
                    'frequency' => 'Sesudah makan',
                    'duration' => '5 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('queue.display.pharmacy'))->assertOk();
        $this->actingAs($user)->get(route('queue.display.pharmacy.json'))
            ->assertOk()
            ->assertJson(['total_patients' => 1]);
    }

    public function test_admin_can_open_reports_and_export_pdf(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('reports.index'))->assertOk()->assertSee('Nilai Stok Obat');
        $this->actingAs($user)->get(route('reports.pdf'))->assertOk();
        $this->actingAs($user)->get(route('reports.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_pharmacist_can_open_stock_mutation_history(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();

        $this->actingAs($pharmacist)->get(route('medicines.mutations'))->assertOk();
    }

    public function test_pharmacist_can_export_stock_mutations_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();

        $this->actingAs($pharmacist)->get(route('medicines.mutations.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_pharmacist_can_export_medicine_catalog_csv(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();

        $this->actingAs($pharmacist)->get(route('medicines.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_stock_mutation_recorded_on_stock_in(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-TEST',
            'quantity' => 25,
            'expiry_date' => now()->addYear()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_mutations', [
            'medicine_id' => $medicine->id,
            'type' => 'in',
            'quantity' => 25,
        ]);
    }

    public function test_registration_can_open_today_queue_board(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->actingAs($registration)->get(route('appointments.queue'))
            ->assertOk()
            ->assertSee('Cetak Tiket');
    }

    public function test_call_patient_from_queue_board_updates_status(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $this->actingAs($user)->patch(route('appointments.status.update', $appointment), [
            'status' => 'in_progress',
            'back' => 'queue',
        ])->assertSessionHasNoErrors();

        $this->assertEquals('in_progress', $appointment->fresh()->status);
    }

    public function test_admin_can_toggle_user_active_status(): void
    {
        $user = $this->seedAdmin();

        $target = User::where('email', 'kasir@his.local')->firstOrFail();

        $this->actingAs($user)->patch(route('users.toggle-active', $target))->assertSessionHasNoErrors();
        $this->assertFalse($target->fresh()->is_active);

        $this->actingAs($user)->patch(route('users.toggle-active', $target))->assertSessionHasNoErrors();
        $this->assertTrue($target->fresh()->is_active);
    }

    public function test_admin_can_export_users_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('users.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_admin_can_export_polis_and_doctors_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('polis.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $this->actingAs($user)->get(route('doctors.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_admin_can_export_schedules_and_tariffs_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('schedules.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $this->actingAs($user)->get(route('tariffs.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $cashier->update(['is_active' => false]);

        $this->post('/login', [
            'email' => 'kasir@his.local',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_new_patient_gets_rm_number_and_insurance(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('patients.store'), [
            'name' => 'Pasien Tes RM',
            'nik' => '3201234567890123',
            'date_of_birth' => '1990-05-10',
            'gender' => 'L',
            'phone_number' => '081234567890',
            'address' => 'Jl. Merdeka No. 1',
            'insurance_provider' => 'BPJS Kesehatan',
            'insurance_number' => 'BPJS-12345',
        ])->assertSessionHasNoErrors();

        $patient = Patient::where('nik', '3201234567890123')->firstOrFail();

        $this->assertMatchesRegularExpression('/^RM-\d{4}-\d{5}$/', $patient->rm_number);
        $this->assertEquals('BPJS Kesehatan', $patient->insurance_provider);
        $this->assertEquals('BPJS-12345', $patient->insurance_number);
    }

    public function test_lab_request_stores_doctor_from_appointment_not_auth_user(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek laboratorium.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();

        $this->assertEquals($appointment->doctor_id, $labRequest->doctor_id);
        $this->assertDatabaseHas('doctors', ['id' => $labRequest->doctor_id]);
    }

    public function test_pharmacist_can_open_pending_prescription_queue(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();

        $this->actingAs($pharmacist)->get(route('prescriptions.pending'))->assertOk();
    }

    public function test_prescription_creation_notifies_pharmacists(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::first();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Pusing.',
            'objective' => 'Tensi normal.',
            'assessment' => 'Vertigo.',
            'plan' => 'Terapi obat.',
            'chief_complaint' => 'Pusing',
            'diagnoses' => [
                ['icd_code' => 'R42', 'description' => 'Dizziness and giddiness', 'is_primary' => 1],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 5,
                    'dosage' => '2x1',
                    'frequency' => 'Sesudah makan',
                    'duration' => '3 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();
        $this->assertGreaterThan(0, $pharmacist->notifications()->count());
    }

    public function test_billing_creation_notifies_cashiers(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::first();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Batuk.',
            'objective' => 'Suhu normal.',
            'assessment' => 'ISPA.',
            'plan' => 'Obat simptomatik.',
            'chief_complaint' => 'Batuk',
            'diagnoses' => [
                ['icd_code' => 'J00', 'description' => 'Acute nasopharyngitis', 'is_primary' => 1],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 5,
                    'dosage' => '3x1',
                    'frequency' => 'Sesudah makan',
                    'duration' => '5 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $record = MedicalRecord::where('appointment_id', $appointment->id)->firstOrFail();

        $this->actingAs($user)->post(route('prescriptions.dispense', $record->prescriptions()->first()))
            ->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee,
            'medicine_fee' => 10000,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertGreaterThan(0, $cashier->notifications()->count());
    }

    public function test_dispensing_prescription_notifies_cashiers(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::first();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Demam.',
            'objective' => 'Suhu 37,8.',
            'assessment' => 'ISPA.',
            'plan' => 'Obat simptomatik.',
            'chief_complaint' => 'Demam',
            'diagnoses' => [
                ['icd_code' => 'J00', 'description' => 'Acute nasopharyngitis', 'is_primary' => 1],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 3,
                    'dosage' => '3x1',
                    'frequency' => 'Sesudah makan',
                    'duration' => '1 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $record = MedicalRecord::where('appointment_id', $appointment->id)->firstOrFail();

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $before = $cashier->notifications()->count();

        $this->actingAs($user)->post(route('prescriptions.dispense', $record->prescriptions()->first()))
            ->assertSessionHasNoErrors();

        $this->assertGreaterThan($before, $cashier->notifications()->count());
    }

    public function test_billing_includes_lab_fees(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek darah lengkap.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();
        $labTotal = $labRequest->items()->sum('price');

        $this->actingAs($user)->get(route('billings.create', $appointment))
            ->assertOk()
            ->assertSee('Biaya Laboratorium');

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee,
            'medicine_fee' => 0,
            'lab_fee' => $labTotal,
            'action_fee' => 0,
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $this->assertNotNull($billing);
        $this->assertTrue(
            $billing->billingItems->contains(fn ($item) => $item->type === 'lab')
        );
        $this->assertGreaterThan(0, (float) $billing->total_amount);
    }

    public function test_billing_includes_selected_tariffs(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $tariff = Tariff::where('type', 'tindakan')->firstOrFail();

        $this->actingAs($user)->get(route('billings.create', $appointment))
            ->assertOk()
            ->assertSee('Tarif Tindakan / Penunjang');

        $this->actingAs($user)->post(route('billings.store'), [
            'appointment_id' => $appointment->id,
            'consultation_fee' => $appointment->consultation_fee,
            'medicine_fee' => 0,
            'lab_fee' => 0,
            'action_fee' => 0,
            'tariff_ids' => [$tariff->id],
            'discount' => 0,
        ])->assertSessionHasNoErrors();

        $billing = $appointment->billing;
        $this->assertNotNull($billing);
        $this->assertTrue(
            $billing->billingItems->contains(fn ($item) => $item->description === $tariff->name)
        );
        $this->assertSame(
            (float) $appointment->consultation_fee + (float) $tariff->price,
            (float) $billing->total_amount
        );
    }

    public function test_notifications_unread_count_endpoint(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();

        $this->actingAs($pharmacist)->get(route('notifications.unread-count'))
            ->assertOk()
            ->assertJsonStructure(['count']);
    }

    public function test_notifications_page_and_sidebar_accessible(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->actingAs($registration)->get(route('notifications.index'))->assertOk();
    }

    public function test_appointment_with_downstream_records_cannot_be_deleted(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::first();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Sakit perut.',
            'objective' => 'Perut kembung.',
            'assessment' => 'Dispepsia.',
            'plan' => 'Obat maag.',
            'chief_complaint' => 'Sakit perut',
            'diagnoses' => [
                ['icd_code' => 'K30', 'description' => 'Dyspepsia', 'is_primary' => 1],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 5,
                    'dosage' => '2x1',
                    'frequency' => 'Sebelum makan',
                    'duration' => '5 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->delete(route('appointments.destroy', $appointment))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('appointments', ['id' => $appointment->id]);
        $this->assertDatabaseHas('medical_records', ['appointment_id' => $appointment->id]);
    }

    public function test_appointment_without_downstream_records_can_be_deleted(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::whereDoesntHave('medicalRecord')
            ->whereDoesntHave('billing')
            ->whereDoesntHave('labRequests')
            ->firstOrFail();

        $this->actingAs($user)->delete(route('appointments.destroy', $appointment))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
    }

    public function test_doctor_can_view_my_patients_today(): void
    {
        $this->seed(DatabaseSeeder::class);

        $doctorUser = User::where('email', 'dokter@his.local')->firstOrFail();

        $this->actingAs($doctorUser)->get(route('appointments.my-patients'))
            ->assertOk()
            ->assertSee('Menunggu')
            ->assertSee('Sedang Diperiksa')
            ->assertSee('Total Pasien');
    }

    public function test_doctor_my_patients_shows_lab_status(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek lab.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();

        $doctorUser = User::where('email', 'dokter@his.local')->firstOrFail();
        $this->actingAs($doctorUser)->get(route('appointments.my-patients'))
            ->assertOk()
            ->assertSee('Proses');

        $items = $labRequest->items->mapWithKeys(function ($item) {
            return [$item->id => ['result_value' => 'Positif', 'result_status' => 'abnormal']];
        })->all();

        $this->actingAs($user)->post(route('lab.requests.process', $labRequest), [
            'status' => 'completed',
            'items' => $items,
        ])->assertSessionHasNoErrors();

        $this->actingAs($doctorUser)->get(route('appointments.my-patients'))
            ->assertOk()
            ->assertSee('Abnormal');
    }

    public function test_notifications_unread_count_requires_dashboard_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $noRoleUser = User::factory()->create();

        $this->actingAs($noRoleUser)->get(route('notifications.unread-count'))->assertForbidden();
    }

    public function test_doctor_can_be_linked_to_user_account(): void
    {
        $user = $this->seedAdmin();

        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');
        $doctor = Doctor::where('license_number', 'SIP/002/A/2026')->firstOrFail();

        $this->actingAs($user)->put(route('doctors.update', $doctor), [
            'name' => $doctor->name,
            'specialization' => $doctor->specialization,
            'license_number' => $doctor->license_number,
            'user_id' => $doctorUser->id,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame($doctorUser->id, $doctor->fresh()->user_id);
    }

    public function test_doctor_user_cannot_be_linked_to_two_doctors(): void
    {
        $user = $this->seedAdmin();

        $doctorUser = User::where('email', 'dokter@his.local')->firstOrFail();
        $doctors = Doctor::orderBy('id')->get();

        $this->actingAs($user)->put(route('doctors.update', $doctors[0]), [
            'name' => $doctors[0]->name,
            'specialization' => $doctors[0]->specialization,
            'license_number' => $doctors[0]->license_number,
            'user_id' => $doctorUser->id,
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->put(route('doctors.update', $doctors[1]), [
            'name' => $doctors[1]->name,
            'specialization' => $doctors[1]->specialization,
            'license_number' => $doctors[1]->license_number,
            'user_id' => $doctorUser->id,
        ])->assertSessionHasErrors('user_id');
    }

    public function test_lab_completion_notifies_requesting_doctor(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek lab.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();

        $items = $labRequest->items->mapWithKeys(function ($item) {
            return [$item->id => [
                'result_value' => 'Positif',
                'result_status' => 'normal',
            ]];
        })->all();

        $this->actingAs($user)->post(route('lab.requests.process', $labRequest), [
            'status' => 'completed',
            'items' => $items,
        ])->assertSessionHasNoErrors();

        $doctorUser = User::where('email', 'dokter@his.local')->firstOrFail();
        $this->assertGreaterThan(0, $doctorUser->notifications()->count());
    }

    public function test_lab_completion_notifies_cashiers_for_billing(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek lab.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();
        $items = $labRequest->items->mapWithKeys(function ($item) {
            return [$item->id => ['result_value' => 'Negatif', 'result_status' => 'normal']];
        })->all();

        $this->actingAs($user)->post(route('lab.requests.process', $labRequest), [
            'status' => 'completed',
            'items' => $items,
        ])->assertSessionHasNoErrors();

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertGreaterThan(0, $cashier->notifications()->count());

        $this->actingAs($user)->get(route('lab.requests.show', $labRequest))
            ->assertOk()
            ->assertSee('Buat Tagihan');
    }

    public function test_medical_record_show_displays_lab_results(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $test = LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek lab.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = LabRequest::where('appointment_id', $appointment->id)->firstOrFail();

        $items = $labRequest->items->mapWithKeys(function ($item) {
            return [$item->id => ['result_value' => 'Positif', 'result_status' => 'abnormal']];
        })->all();

        $this->actingAs($user)->post(route('lab.requests.process', $labRequest), [
            'status' => 'completed',
            'items' => $items,
        ])->assertSessionHasNoErrors();

        $medicalRecord = $appointment->medicalRecord()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'chief_complaint' => 'Demam',
            'subjective' => 'Demam 3 hari',
            'objective' => 'Suhu 38C',
            'assessment' => 'Dengue',
            'plan' => 'Istirahat',
            'status' => 'draft',
        ]);

        $this->actingAs($user)->get(route('medical-records.show', $medicalRecord))
            ->assertOk()
            ->assertSee('Hasil Laboratorium')
            ->assertSee('Abnormal')
            ->assertSee('Positif');

        $this->actingAs($user)->get(route('medical-records.pdf', $medicalRecord))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->get(route('patients.medical-history.pdf', $appointment->patient))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->get(route('patients.medical-history', $appointment->patient))
            ->assertOk()
            ->assertSee('Abnormal');
    }

    public function test_finalized_medical_record_can_export_sick_note(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $medicalRecord = $appointment->medicalRecord()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'chief_complaint' => 'Demam',
            'subjective' => 'Demam 3 hari',
            'objective' => 'Suhu 38C',
            'assessment' => 'Dengue',
            'plan' => 'Istirahat',
            'status' => 'finalized',
        ]);

        Diagnosis::create([
            'medical_record_id' => $medicalRecord->id,
            'icd_code' => 'A90',
            'description' => 'Dengue',
            'is_primary' => true,
        ]);

        $this->actingAs($user)->get(route('medical-records.show', $medicalRecord))
            ->assertOk()
            ->assertSee('Surat Sakit');

        $this->actingAs($user)->get(route('medical-records.sick-note', $medicalRecord))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_medical_record_with_prescription_can_export_prescription_pdf(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::firstOrFail();

        $medicalRecord = $appointment->medicalRecord()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'chief_complaint' => 'Demam',
            'subjective' => 'Demam 3 hari',
            'objective' => 'Suhu 38C',
            'assessment' => 'Dengue',
            'plan' => 'Istirahat',
            'status' => 'finalized',
        ]);

        $prescription = Prescription::create([
            'medical_record_id' => $medicalRecord->id,
            'medicine_id' => $medicine->id,
            'quantity' => 10,
            'dosage' => '3x1',
            'frequency' => 'Sesudah makan',
            'duration' => '5 hari',
            'instructions' => 'Habiskan obat.',
        ]);

        $this->actingAs($user)->get(route('medical-records.show', $medicalRecord))
            ->assertOk()
            ->assertSee('Cetak Resep');

        $this->actingAs($user)->get(route('medical-records.prescription', $medicalRecord))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $prescription->delete();
    }

    public function test_finalized_medical_record_can_export_referral_letter(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();

        $medicalRecord = $appointment->medicalRecord()->create([
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'chief_complaint' => 'Demam',
            'subjective' => 'Demam 3 hari',
            'objective' => 'Suhu 38C',
            'assessment' => 'Dengue',
            'plan' => 'Rujuk',
            'status' => 'finalized',
        ]);

        Diagnosis::create([
            'medical_record_id' => $medicalRecord->id,
            'icd_code' => 'A90',
            'description' => 'Dengue',
            'is_primary' => true,
        ]);

        $this->actingAs($user)->get(route('medical-records.show', $medicalRecord))
            ->assertOk()
            ->assertSee('Surat Rujukan');

        $this->actingAs($user)->get(route('medical-records.referral', ['medicalRecord' => $medicalRecord, 'destination' => 'RS Harapan Sehat']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->get(route('medical-records.referral', $medicalRecord))
            ->assertRedirect();
    }

    public function test_dispense_fails_gracefully_when_stock_insufficient(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::first();
        $medicine = Medicine::firstOrFail();

        MedicineStock::where('medicine_id', $medicine->id)->delete();

        $this->actingAs($user)->post(route('medical-records.store', $appointment), [
            'subjective' => 'Demam.',
            'objective' => 'Suhu 38 C.',
            'assessment' => 'ISPA.',
            'plan' => 'Obat simptomatik.',
            'chief_complaint' => 'Demam',
            'diagnoses' => [
                ['icd_code' => 'J00', 'description' => 'Acute nasopharyngitis', 'is_primary' => 1],
            ],
            'prescriptions' => [
                [
                    'medicine_id' => $medicine->id,
                    'quantity' => 5,
                    'dosage' => '3x1',
                    'frequency' => 'Sesudah makan',
                    'duration' => '5 hari',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $record = MedicalRecord::where('appointment_id', $appointment->id)->firstOrFail();
        $prescription = $record->prescriptions()->firstOrFail();

        $this->actingAs($user)->post(route('prescriptions.dispense', $prescription))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('error');

        $this->assertFalse($prescription->fresh()->is_dispensed);
        $this->assertSame(0, MedicineStock::where('medicine_id', $medicine->id)->sum('quantity'));
    }

    public function test_stock_adjustment_adds_and_deducts_quantity(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-ADJ',
            'quantity' => 10,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('medicine-stocks.adjust'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-ADJ',
            'adjustment_type' => 'in',
            'quantity' => 5,
            'notes' => 'Selisih stok opname (lebih).',
        ])->assertSessionHasNoErrors();

        $batch = MedicineStock::where('medicine_id', $medicine->id)->where('batch_number', 'BATCH-ADJ')->firstOrFail();
        $this->assertSame(15, $batch->quantity);

        $this->actingAs($user)->post(route('medicine-stocks.adjust'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-ADJ',
            'adjustment_type' => 'out',
            'quantity' => 3,
            'notes' => 'Selisih stok opname (kurang).',
        ])->assertSessionHasNoErrors();

        $this->assertSame(12, $batch->fresh()->quantity);

        $this->actingAs($user)->get(route('medicines.mutations'))
            ->assertOk()
            ->assertSee('Selisih stok opname (lebih).');
    }

    public function test_stock_adjustment_rejects_over_deduction(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-ADJ2',
            'quantity' => 2,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('medicine-stocks.adjust'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-ADJ2',
            'adjustment_type' => 'out',
            'quantity' => 50,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, MedicineStock::where('medicine_id', $medicine->id)->where('batch_number', 'BATCH-ADJ2')->firstOrFail()->quantity);
    }

    public function test_doctor_login_lands_on_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', [
            'email' => 'dokter@his.local',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_all_roles_login_lands_on_dashboard(): void
    {
        $this->seed(DatabaseSeeder::class);

        $roles = ['admin', 'registration', 'nurse', 'doctor', 'cashier', 'pharmacist', 'lab_tech'];

        foreach ($roles as $role) {
            $user = User::role($role)->firstOrFail();

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard'));

            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
        }

        $this->assertGuest();
    }

    public function test_appointment_create_json_lookups_return_filtered_data(): void
    {
        $user = $this->seedAdmin();
        $registration = User::role('registration')->firstOrFail();
        $doctor = Doctor::whereHas('schedules', fn ($q) => $q->where('is_active', true))->firstOrFail();
        $schedule = $doctor->schedules()->where('is_active', true)->firstOrFail();

        $doctors = $this->actingAs($registration)
            ->getJson(route('appointments.doctors-by-poli', ['poli_id' => $schedule->poli_id]))
            ->assertOk()
            ->json('doctors');

        $this->assertContains($doctor->id, array_column($doctors, 'id'));

        $dayMap = [1 => 'senin', 2 => 'selasa', 3 => 'rabu', 4 => 'kamis', 5 => 'jumat', 6 => 'sabtu', 0 => 'minggu'];
        $day = $dayMap[Carbon::parse('2026-08-18')->dayOfWeek];

        $schedules = $this->actingAs($registration)
            ->getJson(route('appointments.schedules-by-lookup', [
                'poli_id' => $schedule->poli_id,
                'doctor_id' => $doctor->id,
                'appointment_date' => '2026-08-18',
            ]))
            ->assertOk()
            ->json('schedules');

        $this->assertNotEmpty($schedules);
    }

    public function test_dashboard_shows_reminder_counts(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Antrian Menunggu')
            ->assertSee('Permintaan Lab Pending')
            ->assertSee('Resep Belum Diserahkan')
            ->assertSee('Tagihan Belum Lunas');
    }

    public function test_near_expiry_stock_creation_notifies_pharmacist(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-NEAR-EXP',
            'quantity' => 10,
            'expiry_date' => now()->addDays(30)->toDateString(),
        ])->assertSessionHasNoErrors();

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();
        $this->assertGreaterThan(0, $pharmacist->notifications()->count());
    }

    public function test_retur_stock_records_return_mutation_and_deducts_quantity(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-RETUR',
            'quantity' => 20,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('medicine-stocks.retur'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-RETUR',
            'quantity' => 5,
            'notes' => 'Dikembalikan ke pemasok karena rusak.',
        ])->assertSessionHasNoErrors();

        $batch = MedicineStock::where('medicine_id', $medicine->id)->where('batch_number', 'BATCH-RETUR')->firstOrFail();
        $this->assertSame(15, $batch->quantity);

        $this->assertDatabaseHas('stock_mutations', [
            'medicine_id' => $medicine->id,
            'type' => 'return',
            'quantity' => 5,
        ]);

        $this->actingAs($user)->get(route('medicines.mutations', ['type' => 'return']))
            ->assertOk()
            ->assertSee('Retur / Pengembalian')
            ->assertSee('Dikembalikan ke pemasok karena rusak.');
    }

    public function test_retur_rejects_insufficient_stock(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.store'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-RETUR2',
            'quantity' => 3,
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('medicine-stocks.retur'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-RETUR2',
            'quantity' => 50,
        ])->assertSessionHasErrors('quantity');

        $batch = MedicineStock::where('medicine_id', $medicine->id)->where('batch_number', 'BATCH-RETUR2')->firstOrFail();
        $this->assertSame(3, $batch->quantity);
    }

    public function test_retur_requires_existing_batch(): void
    {
        $user = $this->seedAdmin();

        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('medicine-stocks.retur'), [
            'medicine_id' => $medicine->id,
            'batch_number' => 'BATCH-TIDAK-ADA',
            'quantity' => 1,
        ])->assertSessionHasErrors('batch_number');
    }

    public function test_reports_support_period_aggregation(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('reports.index', ['period' => 'harian']))
            ->assertOk()
            ->assertSee('Tren Pendapatan & Kunjungan per Periode', false)
            ->assertSee('Agregasi: Harian');

        $this->actingAs($user)->get(route('reports.index', ['period' => 'mingguan']))
            ->assertOk()
            ->assertSee('Agregasi: Mingguan');

        $this->actingAs($user)->get(route('reports.index', ['period' => 'bulanan']))
            ->assertOk()
            ->assertSee('Agregasi: Bulanan');
    }

    public function test_reports_period_aggregation_is_included_in_exports(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('reports.pdf', ['period' => 'bulanan']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)->get(route('reports.csv', ['period' => 'mingguan']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_authenticated_user_can_export_patient_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('patients.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_authenticated_user_can_export_medical_records_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('medical-records.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }
}
