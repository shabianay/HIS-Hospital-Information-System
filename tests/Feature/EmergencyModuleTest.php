<?php

namespace Tests\Feature;

use App\Models\EmergencyVisit;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmergencyModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_emergency_pages(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('emergency.index'))->assertOk()->assertSee('IGD & Triase');
        $this->actingAs($user)->get(route('emergency.create'))->assertOk()->assertSee('Daftar Pasien IGD');
    }

    public function test_admin_can_export_emergency_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('emergency.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_create_emergency_visit(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::firstOrFail();

        $this->actingAs($user)->post(route('emergency.store'), [
            'patient_id' => $patient->id,
            'triage_level' => 'red',
            'chief_complaint' => 'Nyeri dada kiri menjalar ke lengan',
            'triage_notes' => 'Pasien gelisah, diaforesis.',
            'temperature' => 37.2,
            'blood_pressure_systolic' => 160,
            'blood_pressure_diastolic' => 95,
            'heart_rate' => 110,
            'respiratory_rate' => 24,
            'oxygen_saturation' => 94,
            'gcs' => 15,
        ])->assertSessionHasNoErrors();

        $visit = EmergencyVisit::where('patient_id', $patient->id)->firstOrFail();
        $this->assertStringStartsWith('IGD-', $visit->visit_number);
        $this->assertEquals('red', $visit->triage_level);
        $this->assertEquals('waiting', $visit->status);
        $this->assertSame($user->id, $visit->created_by);
        $this->assertNotNull($visit->arrived_at);
    }

    public function test_emergency_visit_requires_patient_and_triage(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('emergency.store'), [
            'triage_level' => 'green',
            'chief_complaint' => 'Sakit kepala',
        ])->assertSessionHasErrors(['patient_id']);

        $patient = Patient::firstOrFail();
        $this->actingAs($user)->post(route('emergency.store'), [
            'patient_id' => $patient->id,
            'chief_complaint' => 'Sakit kepala',
        ])->assertSessionHasErrors(['triage_level']);
    }

    public function test_admin_can_update_visit_status_and_vitals(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::firstOrFail();

        $visit = EmergencyVisit::create([
            'visit_number' => 'IGD-TEST-0001',
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'triage_level' => 'yellow',
            'chief_complaint' => 'Luka bakar tangan',
            'status' => 'waiting',
            'arrived_at' => now(),
        ]);

        $this->actingAs($user)->get(route('emergency.show', $visit))
            ->assertOk()
            ->assertSee($visit->visit_number);

        $this->actingAs($user)->put(route('emergency.update', $visit), [
            'status' => 'discharged',
            'triage_level' => 'yellow',
            'discharge_notes' => 'Luka ringan, diobati rawat jalan.',
        ])->assertSessionHasNoErrors();

        $visit->refresh();
        $this->assertEquals('discharged', $visit->status);
        $this->assertNotNull($visit->discharged_at);
        $this->assertSame($user->id, $visit->discharged_by);
    }

    public function test_emergency_index_filters_by_status_and_triage(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::firstOrFail();

        EmergencyVisit::create([
            'visit_number' => 'IGD-TEST-0002',
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'triage_level' => 'red',
            'chief_complaint' => 'Sesak napas berat',
            'status' => 'treatment',
            'arrived_at' => now(),
        ]);

        $this->actingAs($user)->get(route('emergency.index', ['status' => 'treatment']))
            ->assertOk()
            ->assertSee($patient->name);

        $this->actingAs($user)->get(route('emergency.index', ['triage_level' => 'red']))
            ->assertOk()
            ->assertSee($patient->name);
    }

    public function test_nurse_role_can_manage_emergency(): void
    {
        $this->seed(DatabaseSeeder::class);

        $nurse = User::where('email', 'perawat@his.local')->firstOrFail();
        $this->assertTrue($nurse->hasPermissionTo('manage-igd'));

        $this->actingAs($nurse)->get(route('emergency.index'))->assertOk();
        $this->actingAs($nurse)->get(route('emergency.create'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_emergency(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertFalse($cashier->hasPermissionTo('manage-igd'));

        $this->actingAs($cashier)->get(route('emergency.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('emergency.create'))->assertForbidden();
    }

    public function test_sidebar_shows_emergency_menu_for_nurse(): void
    {
        $this->seed(DatabaseSeeder::class);

        $nurse = User::where('email', 'perawat@his.local')->firstOrFail();

        $response = $this->actingAs($nurse)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('IGD & Triase');
    }
}