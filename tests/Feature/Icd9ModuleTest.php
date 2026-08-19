<?php

namespace Tests\Feature;

use App\Models\Icd9Procedure;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Icd9ModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_icd9_page(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('icd9.index'))->assertOk()->assertSee('Master ICD-9-CM');
    }

    public function test_admin_can_export_icd9_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('icd9.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_create_icd9_procedure(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('icd9.store'), [
            'code' => '99.99',
            'name' => 'Other Procedures',
            'category' => 'Lainnya',
            'description' => 'Prosedur lain-lain',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('icd9_procedures', ['code' => '99.99', 'name' => 'Other Procedures']);
    }

    public function test_icd9_code_must_be_unique(): void
    {
        $user = $this->seedAdmin();
        $proc = Icd9Procedure::firstOrFail();

        $this->actingAs($user)->post(route('icd9.store'), [
            'code' => $proc->code,
            'name' => 'Duplicate',
        ])->assertSessionHasErrors('code');
    }

    public function test_admin_can_update_and_toggle_icd9_procedure(): void
    {
        $user = $this->seedAdmin();
        $proc = Icd9Procedure::firstOrFail();

        $this->actingAs($user)->put(route('icd9.update', $proc), [
            'code' => $proc->code,
            'name' => $proc->name . ' (Revisi)',
            'category' => $proc->category,
            'is_active' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertEquals($proc->name . ' (Revisi)', $proc->fresh()->name);
        $this->assertFalse($proc->fresh()->is_active);
    }

    public function test_admin_can_delete_icd9_procedure(): void
    {
        $user = $this->seedAdmin();
        $proc = Icd9Procedure::firstOrFail();

        $this->actingAs($user)->delete(route('icd9.destroy', $proc))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('icd9_procedures', ['id' => $proc->id]);
    }

    public function test_icd9_requires_manage_master_data_permission(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertFalse($cashier->hasPermissionTo('manage-master-data'));

        $this->actingAs($cashier)->get(route('icd9.index'))->assertForbidden();
    }

    public function test_sidebar_shows_icd9_menu_for_admin(): void
    {
        $user = $this->seedAdmin();

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('ICD-9-CM');
    }
}