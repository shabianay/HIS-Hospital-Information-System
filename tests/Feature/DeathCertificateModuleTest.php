<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\DeathCertificate;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeathCertificateModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_death_certificates_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('death-certificates.index'))->assertOk()->assertSee('Surat Kematian');
    }

    public function test_admin_can_export_death_certificates_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('death-certificates.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_issue_death_certificate(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $this->actingAs($user)->post(route('death-certificates.store'), [
            'patient_id' => $appointment->patient_id,
            'date_of_death' => now()->toDateTimeString(),
            'place_of_death' => 'Rumah Sakit',
            'cause_of_death' => 'cardiac',
            'diagnosis' => 'Gagal jantung kongestif',
            'doctor_name' => 'dr. Andi',
            'reporter_name' => 'Budi',
            'deceased_relation' => 'Anak kandung',
        ])->assertSessionHasNoErrors();

        $cert = DeathCertificate::where('patient_id', $appointment->patient_id)->firstOrFail();
        $this->assertStringStartsWith('SK-', $cert->certificate_number);
        $this->assertSame('cardiac', $cert->cause_of_death);
        $this->assertSame($user->id, $cert->created_by);
    }

    public function test_certificate_requires_valid_fields(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('death-certificates.store'), [
            'patient_id' => 99999,
            'date_of_death' => 'invalid',
            'place_of_death' => '',
            'cause_of_death' => 'invalid-cause',
        ])->assertSessionHasErrors(['patient_id', 'date_of_death', 'place_of_death', 'cause_of_death']);
    }

    public function test_admin_can_view_printable_certificate(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $cert = DeathCertificate::create([
            'certificate_number' => 'SK-TEST-0001',
            'patient_id' => $appointment->patient_id,
            'date_of_death' => now(),
            'place_of_death' => 'Rumah Sakit',
            'cause_of_death' => 'natural',
            'doctor_name' => 'dr. Andi',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->get(route('death-certificates.pdf', $cert))
            ->assertOk()
            ->assertSee('SURAT KETERANGAN KEMATIAN')
            ->assertSee($cert->certificate_number);
    }

    public function test_admin_can_delete_certificate(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $cert = DeathCertificate::create([
            'certificate_number' => 'SK-TEST-0002',
            'patient_id' => $appointment->patient_id,
            'date_of_death' => now(),
            'place_of_death' => 'Rumah Sakit',
            'cause_of_death' => 'natural',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('death-certificates.destroy', $cert))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('death_certificates', ['id' => $cert->id]);
    }

    public function test_doctor_and_registration_can_manage_certificates(): void
    {
        $this->seed(DatabaseSeeder::class);

        $doctor = User::where('email', 'dokter@his.local')->firstOrFail();
        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $this->assertTrue($doctor->hasPermissionTo('manage-death-certificate'));
        $this->assertTrue($registration->hasPermissionTo('manage-death-certificate'));

        $this->actingAs($doctor)->get(route('death-certificates.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_certificates(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();
        $this->actingAs($labTech)->get(route('death-certificates.index'))->assertForbidden();
    }
}