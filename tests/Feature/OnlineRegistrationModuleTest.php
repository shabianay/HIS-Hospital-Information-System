<?php

namespace Tests\Feature;

use App\Models\OnlineRegistration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineRegistrationModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_guest_can_open_portal(): void
    {
        $this->get(route('portal.index'))->assertOk()->assertSee('Antrian Online');
    }

    public function test_guest_can_book_queue(): void
    {
        $this->post(route('portal.book'), [
            'patient_name' => 'Budi Santoso',
            'nik' => '3201010101010001',
            'phone' => '08123456789',
            'gender' => 'L',
            'poli' => 'Umum',
            'registration_date' => now()->addDay()->toDateString(),
            'complaint' => 'Demam dan batuk',
        ])->assertSessionHasNoErrors();

        $reg = OnlineRegistration::where('patient_name', 'Budi Santoso')->firstOrFail();
        $this->assertStringStartsWith('AQ-', $reg->registration_number);
        $this->assertStringStartsWith('A-', $reg->queue_number);
        $this->assertSame('registered', $reg->status);
    }

    public function test_queue_numbers_are_sequential_per_poli(): void
    {
        $poli = 'Anak';
        $date = now()->addDay()->toDateString();

        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('portal.book'), [
                'patient_name' => 'Pasien ' . $i,
                'gender' => 'L',
                'poli' => $poli,
                'registration_date' => $date,
            ])->assertSessionHasNoErrors();
        }

        $numbers = OnlineRegistration::where('poli', $poli)->whereDate('registration_date', $date)
            ->orderBy('id')
            ->pluck('queue_number')
            ->toArray();

        $this->assertSame(['C-001', 'C-002', 'C-003'], $numbers);
    }

    public function test_guest_can_lookup_queue_status(): void
    {
        $this->post(route('portal.book'), [
            'patient_name' => 'Siti Aminah',
            'gender' => 'P',
            'poli' => 'Gigi',
            'registration_date' => now()->addDay()->toDateString(),
        ]);

        $reg = OnlineRegistration::where('patient_name', 'Siti Aminah')->firstOrFail();

        $this->get(route('portal.status', ['registration_number' => $reg->registration_number]))
            ->assertOk()
            ->assertSee($reg->registration_number)
            ->assertSee($reg->queue_number);
    }

    public function test_guest_can_cancel_registration(): void
    {
        $this->post(route('portal.book'), [
            'patient_name' => 'Rudi',
            'gender' => 'L',
            'poli' => 'Umum',
            'registration_date' => now()->addDay()->toDateString(),
        ]);

        $reg = OnlineRegistration::where('patient_name', 'Rudi')->firstOrFail();

        $this->post(route('portal.cancel'), [
            'registration_number' => $reg->registration_number,
        ])->assertSessionHas('success');

        $this->assertSame('cancelled', $reg->refresh()->status);
    }

    public function test_admin_can_open_management_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('online-registrations.index'))->assertOk()->assertSee('Antrian Online');
    }

    public function test_admin_can_check_in_and_complete(): void
    {
        $user = $this->seedAdmin();

        $reg = OnlineRegistration::create([
            'registration_number' => 'AQ-TEST-0001',
            'patient_name' => 'Test Pasien',
            'gender' => 'L',
            'poli' => 'Umum',
            'registration_date' => today(),
            'queue_number' => 'A-001',
            'status' => 'registered',
        ]);

        $this->actingAs($user)->post(route('online-registrations.checkin', $reg))->assertSessionHasNoErrors();
        $this->assertSame('checked_in', $reg->refresh()->status);
        $this->assertNotNull($reg->checked_in_at);

        $this->actingAs($user)->post(route('online-registrations.complete', $reg))->assertSessionHasNoErrors();
        $this->assertSame('completed', $reg->refresh()->status);
    }

    public function test_admin_can_export_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('online-registrations.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_registration_role_can_manage_online_registrations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();
        $this->assertTrue($registration->hasPermissionTo('manage-online-registration'));

        $this->actingAs($registration)->get(route('online-registrations.index'))->assertOk();
    }
}