<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    private function seedUser(string $email): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', $email)->firstOrFail();
    }

    private function sidebarHtml(User $user): string
    {
        $html = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();

        preg_match('/<aside\b.*?<\/aside>/s', $html, $match);

        return $match[0] ?? '';
    }

    private function assertSidebarHas(string $html, string $label): void
    {
        $this->assertStringContainsString('>'.e($label).'</span>', $html, "Sidebar seharusnya menampilkan menu '{$label}'.");
    }

    private function assertSidebarNotHas(string $html, string $label): void
    {
        $this->assertStringNotContainsString('>'.e($label).'</span>', $html, "Sidebar seharusnya TIDAK menampilkan menu '{$label}'.");
    }

    public function test_admin_sidebar_shows_all_module_menus(): void
    {
        $user = $this->seedUser('admin@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Antrian Hari Ini');
        $this->assertSidebarHas($html, 'Rekam Medis');
        $this->assertSidebarHas($html, 'Rawat Inap');
        $this->assertSidebarHas($html, 'IGD & Triase');
        $this->assertSidebarHas($html, 'Jadwal Operasi');
        $this->assertSidebarHas($html, 'Laboratorium');
        $this->assertSidebarHas($html, 'Radiologi');
        $this->assertSidebarHas($html, 'Farmasi');
        $this->assertSidebarHas($html, 'Stock Opname');
        $this->assertSidebarHas($html, 'Kasir / Billing');
        $this->assertSidebarHas($html, 'BPJS (SEP & Klaim)');
        $this->assertSidebarHas($html, 'Imunisasi');
        $this->assertSidebarHas($html, 'Surat Kematian');
        $this->assertSidebarHas($html, 'Antrian Online');
        $this->assertSidebarHas($html, 'Users');
    }

    public function test_registration_sidebar_only_shows_registration_menus(): void
    {
        $user = $this->seedUser('pendaftaran@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Antrian Hari Ini');
        $this->assertSidebarHas($html, 'Pasien');
        $this->assertSidebarHas($html, 'Display Antrian');
        $this->assertSidebarHas($html, 'Antrian Online');
        $this->assertSidebarHas($html, 'Rawat Inap');
        $this->assertSidebarHas($html, 'Surat Kematian');
        $this->assertSidebarNotHas($html, 'Rekam Medis');
        $this->assertSidebarNotHas($html, 'Imunisasi');
        $this->assertSidebarNotHas($html, 'Kasir / Billing');
        $this->assertSidebarNotHas($html, 'Farmasi');
        $this->assertSidebarNotHas($html, 'Jadwal Operasi');
        $this->assertSidebarNotHas($html, 'Users');
    }

    public function test_doctor_sidebar_only_shows_doctor_menus(): void
    {
        $user = $this->seedUser('dokter@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Rekam Medis');
        $this->assertSidebarHas($html, 'Pasien Saya');
        $this->assertSidebarHas($html, 'Jadwal Operasi');
        $this->assertSidebarHas($html, 'Surat Kematian');
        $this->assertSidebarNotHas($html, 'Antrian Hari Ini');
        $this->assertSidebarNotHas($html, 'Pasien');
        $this->assertSidebarNotHas($html, 'Rawat Inap');
        $this->assertSidebarNotHas($html, 'Kasir / Billing');
        $this->assertSidebarNotHas($html, 'Farmasi');
        $this->assertSidebarNotHas($html, 'Users');
        $this->assertSidebarNotHas($html, 'Laporan');
    }

    public function test_nurse_sidebar_only_shows_nurse_menus(): void
    {
        $user = $this->seedUser('perawat@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Antrian Hari Ini');
        $this->assertSidebarHas($html, 'Rekam Medis');
        $this->assertSidebarHas($html, 'Imunisasi');
        $this->assertSidebarHas($html, 'Rawat Inap');
        $this->assertSidebarHas($html, 'IGD & Triase');
        $this->assertSidebarHas($html, 'Jadwal Operasi');
        $this->assertSidebarNotHas($html, 'Kasir / Billing');
        $this->assertSidebarNotHas($html, 'Farmasi');
        $this->assertSidebarNotHas($html, 'Pasien Saya');
        $this->assertSidebarNotHas($html, 'Users');
    }

    public function test_cashier_sidebar_only_shows_cashier_menus(): void
    {
        $user = $this->seedUser('kasir@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Kasir / Billing');
        $this->assertSidebarHas($html, 'Pengeluaran');
        $this->assertSidebarHas($html, 'Refund');
        $this->assertSidebarHas($html, 'BPJS (SEP & Klaim)');
        $this->assertSidebarNotHas($html, 'Rekam Medis');
        $this->assertSidebarNotHas($html, 'Farmasi');
        $this->assertSidebarNotHas($html, 'Rawat Inap');
        $this->assertSidebarNotHas($html, 'Users');
    }

    public function test_pharmacist_sidebar_only_shows_pharmacist_menus(): void
    {
        $user = $this->seedUser('apoteker@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Farmasi');
        $this->assertSidebarHas($html, 'Antrian Resep');
        $this->assertSidebarHas($html, 'Pembelian Stok');
        $this->assertSidebarHas($html, 'Kartu Stok');
        $this->assertSidebarHas($html, 'Display Farmasi');
        $this->assertSidebarHas($html, 'Supplier & PO');
        $this->assertSidebarHas($html, 'Stock Opname');
        $this->assertSidebarNotHas($html, 'Rekam Medis');
        $this->assertSidebarNotHas($html, 'Kasir / Billing');
        $this->assertSidebarNotHas($html, 'Rawat Inap');
        $this->assertSidebarNotHas($html, 'Users');
    }

    public function test_lab_tech_sidebar_only_shows_lab_menus(): void
    {
        $user = $this->seedUser('lab@his.local');
        $html = $this->sidebarHtml($user);

        $this->assertSidebarHas($html, 'Laboratorium');
        $this->assertSidebarHas($html, 'Display Lab');
        $this->assertSidebarHas($html, 'Radiologi');
        $this->assertSidebarHas($html, 'Master Pemeriksaan');
        $this->assertSidebarNotHas($html, 'Rekam Medis');
        $this->assertSidebarNotHas($html, 'Farmasi');
        $this->assertSidebarNotHas($html, 'Kasir / Billing');
        $this->assertSidebarNotHas($html, 'Users');
    }
}