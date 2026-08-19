<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_expenses_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('expenses.index'))->assertOk()->assertSee('Pengeluaran');
    }

    public function test_admin_can_export_expenses_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('expenses.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_create_expense(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('expenses.store'), [
            'category' => 'operasional',
            'description' => 'Pembelian ATK bulanan',
            'amount' => 250000,
            'expense_date' => now()->toDateString(),
            'paid_to' => 'Toko Sinar Jaya',
            'payment_method' => 'cash',
            'notes' => 'Kwitansi no. 123',
        ])->assertSessionHasNoErrors();

        $expense = Expense::where('description', 'Pembelian ATK bulanan')->firstOrFail();
        $this->assertStringStartsWith('EXP-', $expense->expense_number);
        $this->assertSame('250000.00', (string) $expense->amount);
        $this->assertSame($user->id, $expense->created_by);
    }

    public function test_expense_requires_valid_fields(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('expenses.store'), [
            'category' => 'invalid-category',
            'description' => 'Test',
            'amount' => -5,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'invalid',
        ])->assertSessionHasErrors(['category', 'amount', 'payment_method']);
    }

    public function test_admin_can_delete_expense(): void
    {
        $user = $this->seedAdmin();

        $expense = Expense::create([
            'expense_number' => 'EXP-TEST-0001',
            'category' => 'operasional',
            'description' => 'Test hapus',
            'amount' => 50000,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('expenses.destroy', $expense))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_cashier_role_can_manage_finance(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertTrue($cashier->hasPermissionTo('manage-finance'));

        $this->actingAs($cashier)->get(route('expenses.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_expenses(): void
    {
        $this->seed(DatabaseSeeder::class);

        $labTech = User::where('email', 'lab@his.local')->firstOrFail();
        $this->assertFalse($labTech->hasPermissionTo('manage-finance'));

        $this->actingAs($labTech)->get(route('expenses.index'))->assertForbidden();
    }

    public function test_sidebar_shows_expenses_menu_for_cashier(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();

        $response = $this->actingAs($cashier)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Pengeluaran');
    }
}