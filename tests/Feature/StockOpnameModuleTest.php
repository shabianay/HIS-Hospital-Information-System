<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\StockMutation;
use App\Models\StockOpname;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockOpnameModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    private function createMedicine(int $qty): Medicine
    {
        $medicine = Medicine::create([
            'name' => 'Parasetamol ' . uniqid(),
            'generic_name' => 'Paracetamol',
            'category' => 'tablet',
            'unit' => 'strip',
            'buy_price' => 5000,
            'sell_price' => 7500,
            'minimum_stock' => 10,
            'is_active' => true,
        ]);

        MedicineStock::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'B-TEST',
            'quantity' => $qty,
            'expiry_date' => now()->addYear(),
        ]);

        return $medicine;
    }

    public function test_admin_can_open_stock_opname_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('stock-opname.index'))->assertOk()->assertSee('Stock Opname');
    }

    public function test_create_page_lists_active_medicines(): void
    {
        $user = $this->seedAdmin();

        $medicine = $this->createMedicine(20);

        $this->actingAs($user)->get(route('stock-opname.create'))
            ->assertOk()
            ->assertSee($medicine->name);
    }

    public function test_admin_can_create_stock_opname(): void
    {
        $user = $this->seedAdmin();

        $medicine = $this->createMedicine(20);

        $this->actingAs($user)->post(route('stock-opname.store'), [
            'opname_date' => now()->toDateString(),
            'items' => [
                ['medicine_id' => $medicine->id, 'actual_quantity' => 18, 'notes' => '2 rusak'],
            ],
        ])->assertSessionHasNoErrors();

        $opname = StockOpname::firstOrFail();
        $this->assertStringStartsWith('OPN-', $opname->opname_number);
        $this->assertSame('draft', $opname->status);

        $item = $opname->items()->firstOrFail();
        $this->assertSame(20, $item->system_quantity);
        $this->assertSame(18, $item->actual_quantity);
        $this->assertSame(-2, $item->difference);
    }

    public function test_admin_can_view_stock_opname_detail(): void
    {
        $user = $this->seedAdmin();

        $medicine = $this->createMedicine(10);

        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-0001',
            'opname_date' => now()->toDateString(),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $opname->items()->create([
            'medicine_id' => $medicine->id,
            'system_quantity' => 10,
            'actual_quantity' => 12,
            'difference' => 2,
        ]);

        $this->actingAs($user)->get(route('stock-opname.show', $opname))
            ->assertOk()
            ->assertSee($medicine->name)
            ->assertSee('+2');
    }

    public function test_admin_can_approve_stock_opname_and_adjust_stock(): void
    {
        $user = $this->seedAdmin();

        $medicine = $this->createMedicine(10);

        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-0002',
            'opname_date' => now()->toDateString(),
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $opname->items()->create([
            'medicine_id' => $medicine->id,
            'system_quantity' => 10,
            'actual_quantity' => 8,
            'difference' => -2,
        ]);

        $this->actingAs($user)->post(route('stock-opname.approve', $opname))
            ->assertSessionHasNoErrors();

        $opname->refresh();
        $this->assertSame('approved', $opname->status);

        $stock = MedicineStock::where('medicine_id', $medicine->id)->firstOrFail();
        $this->assertSame(8, $stock->quantity);

        $this->assertDatabaseHas('stock_mutations', [
            'medicine_id' => $medicine->id,
            'type' => 'out',
            'quantity' => 2,
            'reference' => 'opname#OPN-TEST-0002',
        ]);
    }

    public function test_approved_opname_cannot_be_deleted(): void
    {
        $user = $this->seedAdmin();

        $opname = StockOpname::create([
            'opname_number' => 'OPN-TEST-0003',
            'opname_date' => now()->toDateString(),
            'status' => 'approved',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('stock-opname.destroy', $opname))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('stock_opnames', ['id' => $opname->id]);
    }

    public function test_pharmacist_role_can_manage_stock_opname(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();
        $this->assertTrue($pharmacist->hasPermissionTo('manage-stock-opname'));

        $this->actingAs($pharmacist)->get(route('stock-opname.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_stock_opname(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();
        $this->actingAs($registration)->get(route('stock-opname.index'))->assertForbidden();
    }
}