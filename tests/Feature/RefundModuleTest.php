<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Patient;
use App\Models\Refund;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    private function createPaidBilling(float $paid, float $total): Billing
    {
        $appointment = Appointment::firstOrFail();

        return Billing::create([
            'invoice_number' => 'INV-TEST-' . uniqid(),
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'payment_method' => 'cash',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function test_admin_can_open_refunds_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('refunds.index'))->assertOk()->assertSee('Refund');
    }

    public function test_admin_can_export_refunds_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('refunds.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_create_page_lists_overpaid_billings_only(): void
    {
        $user = $this->seedAdmin();

        $overpaid = $this->createPaidBilling(150000, 100000);
        $exact = $this->createPaidBilling(100000, 100000);

        $this->actingAs($user)->get(route('refunds.create'))
            ->assertOk()
            ->assertSee($overpaid->invoice_number)
            ->assertDontSee($exact->invoice_number);
    }

    public function test_admin_can_process_refund(): void
    {
        $user = $this->seedAdmin();

        $billing = $this->createPaidBilling(200000, 150000);

        $this->actingAs($user)->post(route('refunds.store'), [
            'billing_id' => $billing->id,
            'amount' => 50000,
            'reason' => 'overpayment',
        ])->assertSessionHasNoErrors();

        $refund = Refund::where('billing_id', $billing->id)->firstOrFail();
        $this->assertStringStartsWith('REF-', $refund->refund_number);
        $this->assertSame('50000.00', (string) $refund->amount);
        $this->assertSame($user->id, $refund->processed_by);

        $billing->refresh();
        $this->assertSame('150000.00', (string) $billing->paid_amount);
    }

    public function test_refund_cannot_exceed_overpayment(): void
    {
        $user = $this->seedAdmin();

        $billing = $this->createPaidBilling(200000, 150000);

        $this->actingAs($user)->post(route('refunds.store'), [
            'billing_id' => $billing->id,
            'amount' => 100000,
            'reason' => 'overpayment',
        ])->assertSessionHasErrors(['amount']);

        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_refund_rejected_when_no_overpayment(): void
    {
        $user = $this->seedAdmin();

        $billing = $this->createPaidBilling(150000, 150000);

        $this->actingAs($user)->post(route('refunds.store'), [
            'billing_id' => $billing->id,
            'amount' => 10000,
            'reason' => 'overpayment',
        ])->assertSessionHasErrors(['amount']);
    }

    public function test_cashier_can_access_refunds(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertTrue($cashier->hasPermissionTo('manage-finance'));

        $this->actingAs($cashier)->get(route('refunds.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_refunds(): void
    {
        $this->seed(DatabaseSeeder::class);

        $nurse = User::where('email', 'perawat@his.local')->firstOrFail();
        $this->actingAs($nurse)->get(route('refunds.index'))->assertForbidden();
    }
}