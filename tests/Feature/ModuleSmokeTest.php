<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Schedule;
use App\Models\User;
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
        $this->actingAs($user)->get(route('patients.medical-history', $patient))->assertOk();
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
            'payment_method' => 'cash',
            'paid_amount' => $billing->total_amount,
        ])->assertSessionHasNoErrors();

        $billing->refresh();
        $this->assertEquals('paid', $billing->status);

        $this->actingAs($user)->get(route('billings.receipt', $billing))->assertOk();
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

        $regResponse = $this->actingAs($registration)->get(route('dashboard'));
        $regResponse->assertOk()
            ->assertSee(route('appointments.index'))
            ->assertDontSee(route('billings.index'));

        $cashierResponse = $this->actingAs($cashier)->get(route('dashboard'));
        $cashierResponse->assertOk()
            ->assertSee(route('billings.index'))
            ->assertDontSee(route('appointments.index'));
    }

    public function test_admin_can_export_audit_csv_and_pdf(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('audit.export.csv'))->assertOk();
        $this->actingAs($user)->get(route('audit.export.pdf'))->assertOk();
    }

    public function test_admin_can_export_daily_report_pdf(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('billings.daily-report.pdf'))->assertOk();
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
        $test = \App\Models\LabTest::firstOrFail();

        $this->actingAs($user)->post(route('lab.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'notes' => 'Cek darah lengkap.',
            'lab_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $labRequest = \App\Models\LabRequest::where('appointment_id', $appointment->id)->firstOrFail();

        $this->actingAs($user)->get(route('lab.requests.pdf', $labRequest))->assertOk();
    }

    public function test_queue_display_accessible_to_authenticated_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->actingAs($registration)->get(route('queue.display'))->assertOk();
        $this->actingAs($registration)->get(route('queue.display.json'))->assertOk();
    }
}
