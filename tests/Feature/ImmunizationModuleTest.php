<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Immunization;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImmunizationModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_immunizations_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('immunizations.index'))->assertOk()->assertSee('Imunisasi');
    }

    public function test_admin_can_export_immunizations_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('immunizations.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_record_immunization(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $this->actingAs($user)->post(route('immunizations.store'), [
            'patient_id' => $appointment->patient_id,
            'vaccine_name' => 'BCG',
            'dose' => '1',
            'administered_at' => now()->toDateString(),
            'next_due_date' => now()->addMonths(1)->toDateString(),
            'batch_number' => 'B20260801',
            'site' => 'deltoid-kiri',
            'healthcare_worker' => 'dr. Sari',
        ])->assertSessionHasNoErrors();

        $im = Immunization::where('patient_id', $appointment->patient_id)->firstOrFail();
        $this->assertSame('BCG', $im->vaccine_name);
        $this->assertSame('B20260801', $im->batch_number);
        $this->assertSame($user->id, $im->created_by);
    }

    public function test_immunization_requires_valid_fields(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('immunizations.store'), [
            'vaccine_name' => '',
            'administered_at' => 'not-a-date',
        ])->assertSessionHasErrors(['patient_id', 'vaccine_name', 'administered_at']);
    }

    public function test_next_due_date_must_be_after_administered_date(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $this->actingAs($user)->post(route('immunizations.store'), [
            'patient_id' => $appointment->patient_id,
            'vaccine_name' => 'DPT',
            'administered_at' => now()->toDateString(),
            'next_due_date' => now()->subDay()->toDateString(),
        ])->assertSessionHasErrors(['next_due_date']);
    }

    public function test_admin_can_delete_immunization(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $im = Immunization::create([
            'patient_id' => $appointment->patient_id,
            'vaccine_name' => 'BCG',
            'administered_at' => now()->toDateString(),
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('immunizations.destroy', $im))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('immunizations', ['id' => $im->id]);
    }

    public function test_nurse_role_can_manage_immunizations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $nurse = User::where('email', 'perawat@his.local')->firstOrFail();
        $this->assertTrue($nurse->hasPermissionTo('manage-immunization'));

        $this->actingAs($nurse)->get(route('immunizations.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_immunizations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();
        $this->assertFalse($labTech->hasPermissionTo('manage-immunization'));

        $this->actingAs($labTech)->get(route('immunizations.index'))->assertForbidden();
    }
}