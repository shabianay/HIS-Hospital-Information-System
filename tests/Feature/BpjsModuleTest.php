<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BpjsClaim;
use App\Models\SepRecord;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BpjsModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_bpjs_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('bpjs.index'))->assertOk()->assertSee('BPJS');
    }

    public function test_admin_can_export_claims_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('bpjs.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_create_sep(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $this->actingAs($user)->post(route('bpjs.sep.store'), [
            'patient_id' => $appointment->patient_id,
            'bpjs_number' => '0001234567890',
            'jenis_pelayanan' => 'rawat_jalan',
            'sep_date' => now()->toDateString(),
            'diagnosis' => 'Hipertensi',
            'poli' => 'Penyakit Dalam',
            'faskes_perujuk' => 'Puskesmas',
        ])->assertSessionHasNoErrors();

        $sep = SepRecord::where('patient_id', $appointment->patient_id)->firstOrFail();
        $this->assertStringStartsWith('SEP-', $sep->sep_number);
        $this->assertSame('aktif', $sep->status);
        $this->assertSame($user->id, $sep->created_by);
    }

    public function test_admin_can_cancel_sep(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $sep = SepRecord::create([
            'sep_number' => 'SEP-TEST-0001',
            'patient_id' => $appointment->patient_id,
            'bpjs_number' => '0001234567890',
            'jenis_pelayanan' => 'rawat_jalan',
            'sep_date' => now()->toDateString(),
            'status' => 'aktif',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('bpjs.sep.cancel', $sep))
            ->assertSessionHasNoErrors();

        $this->assertSame('dibatalkan', $sep->refresh()->status);
    }

    public function test_admin_can_submit_claim(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $this->actingAs($user)->post(route('bpjs.claim.store'), [
            'patient_id' => $appointment->patient_id,
            'claim_date' => now()->toDateString(),
            'total_claim' => 1500000,
            'jenis_klaim' => 'rawat_jalan',
        ])->assertSessionHasNoErrors();

        $claim = BpjsClaim::where('patient_id', $appointment->patient_id)->firstOrFail();
        $this->assertStringStartsWith('KLM-', $claim->claim_number);
        $this->assertSame('diajukan', $claim->status);
        $this->assertSame('1500000.00', (string) $claim->total_claim);
    }

    public function test_admin_can_update_claim_status_to_approved(): void
    {
        $user = $this->seedAdmin();

        $appointment = Appointment::firstOrFail();

        $claim = BpjsClaim::create([
            'claim_number' => 'KLM-TEST-0001',
            'patient_id' => $appointment->patient_id,
            'claim_date' => now()->toDateString(),
            'total_claim' => 1000000,
            'status' => 'diajukan',
            'jenis_klaim' => 'rawat_jalan',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->post(route('bpjs.claim.status', $claim), [
            'status' => 'disetujui',
            'approved_amount' => 800000,
        ])->assertSessionHasNoErrors();

        $claim->refresh();
        $this->assertSame('disetujui', $claim->status);
        $this->assertSame('800000.00', (string) $claim->approved_amount);
    }

    public function test_claim_requires_valid_fields(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('bpjs.claim.store'), [
            'patient_id' => 99999,
            'total_claim' => -5,
            'jenis_klaim' => 'invalid',
        ])->assertSessionHasErrors(['patient_id', 'total_claim', 'jenis_klaim', 'claim_date']);
    }

    public function test_cashier_role_can_manage_bpjs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertTrue($cashier->hasPermissionTo('manage-bpjs'));

        $this->actingAs($cashier)->get(route('bpjs.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_bpjs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();
        $this->actingAs($labTech)->get(route('bpjs.index'))->assertForbidden();
    }
}