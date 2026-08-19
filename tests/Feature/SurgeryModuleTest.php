<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\Surgery;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurgeryModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_surgery_pages(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('surgeries.index'))->assertOk()->assertSee('Jadwal Operasi');
        $this->actingAs($user)->get(route('surgeries.create'))->assertOk()->assertSee('Buat Jadwal Operasi');
    }

    public function test_admin_can_export_surgery_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('surgeries.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_create_surgery_schedule(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::firstOrFail();

        $this->actingAs($user)->post(route('surgeries.store'), [
            'patient_id' => $patient->id,
            'procedure_name' => 'Appendectomy',
            'surgery_type' => 'major',
            'operating_room' => 'OK 1',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'pre_notes' => 'Puasa 8 jam sebelum operasi.',
        ])->assertSessionHasNoErrors();

        $surgery = Surgery::where('patient_id', $patient->id)->firstOrFail();
        $this->assertStringStartsWith('OK-', $surgery->surgery_number);
        $this->assertEquals('scheduled', $surgery->status);
        $this->assertEquals('Appendectomy', $surgery->procedure_name);
        $this->assertSame($user->id, $surgery->created_by);
    }

    public function test_surgery_requires_valid_schedule(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::firstOrFail();

        $this->actingAs($user)->post(route('surgeries.store'), [
            'patient_id' => $patient->id,
            'procedure_name' => 'Appendectomy',
            'surgery_type' => 'major',
            'scheduled_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors(['scheduled_at']);

        $this->actingAs($user)->post(route('surgeries.store'), [
            'patient_id' => $patient->id,
            'procedure_name' => 'Appendectomy',
            'surgery_type' => 'major',
            'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors();
    }

    public function test_admin_can_update_surgery_status_flow(): void
    {
        $user = $this->seedAdmin();
        $patient = Patient::firstOrFail();

        $surgery = Surgery::create([
            'surgery_number' => 'OK-TEST-0001',
            'patient_id' => $patient->id,
            'created_by' => $user->id,
            'procedure_name' => 'Laparoscopic Cholecystectomy',
            'surgery_type' => 'major',
            'status' => 'scheduled',
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($user)->get(route('surgeries.show', $surgery))
            ->assertOk()
            ->assertSee($surgery->surgery_number);

        $this->actingAs($user)->patch(route('surgeries.status', $surgery), [
            'status' => 'in_progress',
        ])->assertSessionHasNoErrors();

        $surgery->refresh();
        $this->assertEquals('in_progress', $surgery->status);
        $this->assertNotNull($surgery->started_at);

        $this->actingAs($user)->patch(route('surgeries.status', $surgery), [
            'status' => 'completed',
            'post_notes' => 'Operasi berjalan lancar.',
        ])->assertSessionHasNoErrors();

        $surgery->refresh();
        $this->assertEquals('completed', $surgery->status);
        $this->assertNotNull($surgery->finished_at);
        $this->assertSame($user->id, $surgery->completed_by);
    }

    public function test_doctor_role_can_manage_surgery(): void
    {
        $this->seed(DatabaseSeeder::class);

        $doctor = User::where('email', 'dokter@his.local')->firstOrFail();
        $this->assertTrue($doctor->hasPermissionTo('manage-surgery'));

        $this->actingAs($doctor)->get(route('surgeries.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_surgery(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertFalse($cashier->hasPermissionTo('manage-surgery'));

        $this->actingAs($cashier)->get(route('surgeries.index'))->assertForbidden();
    }

    public function test_sidebar_shows_surgery_menu_for_doctor(): void
    {
        $this->seed(DatabaseSeeder::class);

        $doctor = User::where('email', 'dokter@his.local')->firstOrFail();

        $response = $this->actingAs($doctor)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Jadwal Operasi');
    }
}