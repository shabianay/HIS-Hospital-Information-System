<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\RadiologyRequest;
use App\Models\RadiologyRequestItem;
use App\Models\RadiologyTest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RadiologyModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_radiology_index_pages(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('radiology.tests'))->assertOk()->assertSee('Master Pemeriksaan Radiologi');
        $this->actingAs($user)->get(route('radiology.requests'))->assertOk()->assertSee('Permintaan Radiologi');
    }

    public function test_admin_can_export_radiology_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('radiology.tests.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->actingAs($user)->get(route('radiology.requests.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_admin_can_create_and_toggle_radiology_test(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('radiology.tests.store'), [
            'name' => 'Foto Cervical AP/Lateral',
            'category' => 'Rontgen',
            'unit' => 'proyeksi',
            'reference_range' => 'AP & Lateral',
            'price' => 90000,
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('radiology_tests', ['name' => 'Foto Cervical AP/Lateral']);

        $test = RadiologyTest::where('name', 'Foto Cervical AP/Lateral')->firstOrFail();
        $this->assertSame('90000.00', (string) $test->price);

        $this->actingAs($user)->put(route('radiology.tests.update', $test), [
            'name' => 'Foto Cervical AP/Lateral',
            'category' => 'Rontgen',
            'unit' => 'proyeksi',
            'price' => 95000,
            'is_active' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($test->fresh()->is_active);
        $this->assertSame('95000.00', (string) $test->fresh()->price);
    }

    public function test_admin_can_delete_radiology_test(): void
    {
        $user = $this->seedAdmin();
        $test = RadiologyTest::firstOrFail();

        $this->actingAs($user)->delete(route('radiology.tests.destroy', $test))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('radiology_tests', ['id' => $test->id]);
    }

    public function test_admin_can_create_radiology_request(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::firstOrFail();
        $test = RadiologyTest::firstOrFail();

        $this->actingAs($user)->post(route('radiology.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'is_urgent' => 1,
            'clinical_notes' => 'Suspek fraktur colles.',
            'radiology_test_ids' => [$test->id],
        ])->assertSessionHasNoErrors();

        $requestRecord = RadiologyRequest::where('patient_id', $appointment->patient_id)->firstOrFail();
        $this->assertEquals('pending', $requestRecord->status);
        $this->assertTrue($requestRecord->is_urgent);
        $this->assertSame($user->id, $requestRecord->created_by);

        $this->assertDatabaseHas('radiology_request_items', [
            'radiology_request_id' => $requestRecord->id,
            'radiology_test_id' => $test->id,
            'test_name' => $test->name,
        ]);
    }

    public function test_radiology_request_requires_at_least_one_test(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::firstOrFail();

        $this->actingAs($user)->post(route('radiology.requests.store'), [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'radiology_test_ids' => [],
        ])->assertSessionHasErrors('radiology_test_ids');
    }

    public function test_admin_can_enter_results_and_complete_request(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::firstOrFail();
        $test = RadiologyTest::firstOrFail();

        $requestRecord = RadiologyRequest::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'created_by' => $user->id,
            'is_urgent' => false,
            'status' => 'in_progress',
            'clinical_notes' => 'Foto thorax suspek pneumonia.',
        ]);

        $item = RadiologyRequestItem::create([
            'radiology_request_id' => $requestRecord->id,
            'radiology_test_id' => $test->id,
            'test_name' => $test->name,
            'price' => $test->price,
            'result_status' => 'pending',
        ]);

        $this->actingAs($user)->post(route('radiology.requests.process', $requestRecord), [
            'items' => [
                $item->id => [
                    'result_findings' => 'Infiltrat di lapangan paru kanan.',
                    'result_impression' => 'Suspek pneumonia lobus kanan.',
                    'result_status' => 'abnormal',
                ],
            ],
            'status' => 'completed',
        ])->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertEquals('abnormal', $item->result_status);
        $this->assertStringContainsString('Infiltrat', $item->result_findings);

        $requestRecord->refresh();
        $this->assertEquals('completed', $requestRecord->status);
        $this->assertNotNull($requestRecord->completed_at);
    }

    public function test_admin_can_open_request_detail_and_download_pdf(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::firstOrFail();
        $test = RadiologyTest::firstOrFail();

        $requestRecord = RadiologyRequest::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'created_by' => $user->id,
            'is_urgent' => false,
            'status' => 'pending',
        ]);

        RadiologyRequestItem::create([
            'radiology_request_id' => $requestRecord->id,
            'radiology_test_id' => $test->id,
            'test_name' => $test->name,
            'price' => $test->price,
            'result_status' => 'pending',
        ]);

        $this->actingAs($user)->get(route('radiology.requests.show', $requestRecord))
            ->assertOk()
            ->assertSee($test->name);

        $this->actingAs($user)->get(route('radiology.requests.pdf', $requestRecord))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_radiology_requests_index_filters_by_status(): void
    {
        $user = $this->seedAdmin();
        $appointment = Appointment::firstOrFail();

        RadiologyRequest::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'created_by' => $user->id,
            'is_urgent' => false,
            'status' => 'completed',
        ]);

        $this->actingAs($user)->get(route('radiology.requests', ['status' => 'completed']))
            ->assertOk()
            ->assertSee($appointment->patient->name);

        $this->actingAs($user)->get(route('radiology.requests', ['status' => 'pending']))
            ->assertOk();
    }

    public function test_lab_tech_role_can_manage_radiology(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();
        $this->assertTrue($labTech->hasPermissionTo('manage-radiology'));

        $this->actingAs($labTech)->get(route('radiology.requests'))->assertOk();
        $this->actingAs($labTech)->get(route('radiology.tests'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_radiology(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertFalse($cashier->hasPermissionTo('manage-radiology'));

        $this->actingAs($cashier)->get(route('radiology.requests'))->assertForbidden();
        $this->actingAs($cashier)->get(route('radiology.tests'))->assertForbidden();
    }

    public function test_sidebar_shows_radiology_menu_for_lab_tech(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();

        $response = $this->actingAs($labTech)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Radiologi')
            ->assertSee('Master Pemeriksaan');
    }
}
