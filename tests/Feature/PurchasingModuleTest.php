<?php

namespace Tests\Feature;

use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\PurchaseOrder;
use App\Models\StockMutation;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_purchasing_pages(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('purchasing.suppliers'))->assertOk()->assertSee('Supplier');
        $this->actingAs($user)->get(route('purchasing.orders'))->assertOk()->assertSee('Purchase Order');
        $this->actingAs($user)->get(route('purchasing.orders.create'))->assertOk()->assertSee('Buat Purchase Order');
    }

    public function test_admin_can_export_purchasing_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('purchasing.suppliers.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->actingAs($user)->get(route('purchasing.orders.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_create_and_toggle_supplier(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('purchasing.suppliers.store'), [
            'name' => 'PT Sehat Abadi',
            'contact_person' => 'Bpk. Toni',
            'phone' => '021-5559999',
            'email' => 'toni@sehatabadi.co.id',
            'address' => 'Jl. Melati No. 3',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('suppliers', ['name' => 'PT Sehat Abadi']);

        $supplier = Supplier::where('name', 'PT Sehat Abadi')->firstOrFail();
        $this->actingAs($user)->put(route('purchasing.suppliers.update', $supplier), [
            'name' => 'PT Sehat Abadi',
            'is_active' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($supplier->fresh()->is_active);
    }

    public function test_admin_can_create_purchase_order(): void
    {
        $user = $this->seedAdmin();
        $supplier = Supplier::firstOrFail();
        $medicine = Medicine::firstOrFail();

        $this->actingAs($user)->post(route('purchasing.orders.store'), [
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'notes' => 'Restok rutin bulanan',
            'items' => [
                ['medicine_id' => $medicine->id, 'quantity' => 50, 'unit_price' => 1200],
            ],
        ])->assertSessionHasNoErrors();

        $order = PurchaseOrder::where('supplier_id', $supplier->id)->firstOrFail();
        $this->assertStringStartsWith('PO-', $order->po_number);
        $this->assertEquals('draft', $order->status);
        $this->assertSame('60000.00', (string) $order->total_amount);
        $this->assertSame($user->id, $order->created_by);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $order->id,
            'medicine_id' => $medicine->id,
            'quantity' => 50,
        ]);
    }

    public function test_purchase_order_requires_items(): void
    {
        $user = $this->seedAdmin();
        $supplier = Supplier::firstOrFail();

        $this->actingAs($user)->post(route('purchasing.orders.store'), [
            'supplier_id' => $supplier->id,
            'order_date' => now()->toDateString(),
            'items' => [],
        ])->assertSessionHasErrors('items');
    }

    public function test_receiving_po_adds_stock_and_mutation(): void
    {
        $user = $this->seedAdmin();
        $supplier = Supplier::firstOrFail();
        $medicine = Medicine::firstOrFail();

        $order = PurchaseOrder::create([
            'po_number' => 'PO-TEST-0001',
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'status' => 'ordered',
            'order_date' => now()->toDateString(),
            'total_amount' => 60000,
        ]);

        \App\Models\PurchaseOrderItem::create([
            'purchase_order_id' => $order->id,
            'medicine_id' => $medicine->id,
            'quantity' => 50,
            'unit_price' => 1200,
            'line_total' => 60000,
        ]);

        $beforeQty = MedicineStock::where('medicine_id', $medicine->id)->sum('quantity');

        $this->actingAs($user)->patch(route('purchasing.orders.status', $order), [
            'status' => 'received',
        ])->assertSessionHasNoErrors();

        $order->refresh();
        $this->assertEquals('received', $order->status);
        $this->assertNotNull($order->received_at);

        $afterQty = MedicineStock::where('medicine_id', $medicine->id)->sum('quantity');
        $this->assertEquals($beforeQty + 50, $afterQty);

        $this->assertDatabaseHas('stock_mutations', [
            'medicine_id' => $medicine->id,
            'type' => 'in',
            'quantity' => 50,
            'reference' => "po#{$order->id}",
        ]);
    }

    public function test_received_po_cannot_be_deleted(): void
    {
        $user = $this->seedAdmin();
        $supplier = Supplier::firstOrFail();

        $order = PurchaseOrder::create([
            'po_number' => 'PO-TEST-0002',
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'status' => 'received',
            'order_date' => now()->toDateString(),
            'total_amount' => 0,
            'received_at' => now(),
        ]);

        $this->actingAs($user)->delete(route('purchasing.orders.destroy', $order));

        $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
    }

    public function test_pharmacist_role_can_manage_purchasing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();
        $this->assertTrue($pharmacist->hasPermissionTo('manage-purchasing'));

        $this->actingAs($pharmacist)->get(route('purchasing.orders'))->assertOk();
        $this->actingAs($pharmacist)->get(route('purchasing.suppliers'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_purchasing(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertFalse($cashier->hasPermissionTo('manage-purchasing'));

        $this->actingAs($cashier)->get(route('purchasing.orders'))->assertForbidden();
    }

    public function test_sidebar_shows_purchasing_menu_for_pharmacist(): void
    {
        $this->seed(DatabaseSeeder::class);

        $pharmacist = User::where('email', 'apoteker@his.local')->firstOrFail();

        $response = $this->actingAs($pharmacist)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Supplier & PO');
    }
}