<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InpatientModuleTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdmin(): User
    {
        $this->seed(DatabaseSeeder::class);

        return User::where('email', 'admin@his.local')->firstOrFail();
    }

    public function test_admin_can_open_inpatient_index_pages(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('rooms.index'))->assertOk()->assertSee('Kamar Rawat Inap');
        $this->actingAs($user)->get(route('beds.index'))->assertOk()->assertSee('Tempat Tidur');
        $this->actingAs($user)->get(route('admissions.index'))->assertOk()->assertSee('Rawat Inap');
    }

    public function test_admin_can_open_inpatient_create_pages(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('rooms.create'))->assertOk()->assertSee('Tambah Kamar Baru');
        $this->actingAs($user)->get(route('beds.create'))->assertOk()->assertSee('Tambah Tempat Tidur Baru');
        $this->actingAs($user)->get(route('admissions.create'))->assertOk()->assertSee('Registrasi Rawat Inap Baru');
    }

    public function test_admin_can_export_inpatient_csv(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->get(route('rooms.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->actingAs($user)->get(route('beds.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->actingAs($user)->get(route('admissions.index.csv'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_room_crud_flow(): void
    {
        $user = $this->seedAdmin();

        $this->actingAs($user)->post(route('rooms.store'), [
            'code' => 'K1-02',
            'name' => 'Kelas 1 - Ruang B',
            'room_type' => 'class_1',
            'price_per_day' => 425000,
            'description' => 'Kamar kelas 1 tambahan',
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', ['code' => 'K1-02', 'name' => 'Kelas 1 - Ruang B']);

        $room = Room::where('code', 'K1-02')->firstOrFail();
        $this->assertSame('425000.00', (string) $room->price_per_day);

        $this->actingAs($user)->put(route('rooms.update', $room), [
            'code' => 'K1-02',
            'name' => 'Kelas 1 - Ruang B (Revisi)',
            'room_type' => 'class_1',
            'price_per_day' => 450000,
            'description' => 'Revisi',
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertEquals('Kelas 1 - Ruang B (Revisi)', $room->fresh()->name);

        $this->actingAs($user)->get(route('rooms.show', $room))->assertOk()->assertSee('Kelas 1 - Ruang B (Revisi)');
    }

    public function test_room_with_admissions_cannot_be_deleted(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();
        $patient = Patient::firstOrFail();
        $doctor = Doctor::firstOrFail();
        $bed = $room->beds()->firstOrFail();

        Admission::create([
            'admission_number' => 'INAP-TEST-0001',
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'elective',
            'status' => 'admitted',
            'admitted_at' => now(),
        ]);

        $this->actingAs($user)->delete(route('rooms.destroy', $room))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    public function test_bed_crud_and_unique_per_room(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();

        $this->actingAs($user)->post(route('beds.store'), [
            'room_id' => $room->id,
            'bed_number' => '99',
            'is_active' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('beds', ['room_id' => $room->id, 'bed_number' => '99']);

        $this->actingAs($user)->post(route('beds.store'), [
            'room_id' => $room->id,
            'bed_number' => '99',
            'is_active' => 1,
        ])->assertSessionHasErrors('bed_number');
    }

    public function test_creating_admission_occupies_a_bed(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();
        $bed = $room->beds()->firstOrFail();
        $patient = Patient::firstOrFail();
        $doctor = Doctor::firstOrFail();

        $this->actingAs($user)->post(route('admissions.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'elective',
            'admitted_at' => now()->format('Y-m-d\TH:i'),
            'diagnosis' => 'Observasi demam',
])->assertSessionHasNoErrors();

        $admission = Admission::where('patient_id', $patient->id)->firstOrFail();
        $this->assertEquals('admitted', $admission->status);
        $this->assertStringStartsWith('INAP-', $admission->admission_number);
        $this->assertSame($user->id, $admission->admitted_by);

        $this->assertFalse($bed->fresh()->isAvailable());
    }

public function test_cannot_admit_patient_already_admitted(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();
        $bed = $room->beds()->firstOrFail();
        $patient = Patient::firstOrFail();
        $doctor = Doctor::firstOrFail();

        $payload = [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'elective',
            'admitted_at' => now()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($user)->post(route('admissions.store'), $payload)->assertSessionHasNoErrors();

        $secondBed = $room->beds()->where('id', '!=', $bed->id)->firstOrFail();

        $this->actingAs($user)->post(route('admissions.store'), array_merge($payload, ['bed_id' => $secondBed->id]))
            ->assertSessionHasErrors('patient_id');
    }

    public function test_cannot_admit_to_occupied_bed(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();
        $bed = $room->beds()->firstOrFail();
        $patients = Patient::orderBy('id')->get();
        $doctor = Doctor::firstOrFail();

        $payload = [
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'elective',
            'admitted_at' => now()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($user)->post(route('admissions.store'), $payload + ['patient_id' => $patients[0]->id])
            ->assertSessionHasNoErrors();

        $this->actingAs($user)->post(route('admissions.store'), $payload + ['patient_id' => $patients[1]->id])
            ->assertSessionHasErrors('bed_id');
    }

    public function test_cannot_use_bed_from_different_room(): void
    {
        $user = $this->seedAdmin();
        $room = Room::orderBy('id')->firstOrFail();
        $otherRoom = Room::orderBy('id')->skip(1)->firstOrFail();
        $bed = $otherRoom->beds()->firstOrFail();
        $patient = Patient::firstOrFail();
        $doctor = Doctor::firstOrFail();

        $this->actingAs($user)->post(route('admissions.store'), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'elective',
            'admitted_at' => now()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('bed_id');
    }

    public function test_admission_can_be_discharged_freeing_the_bed(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();
        $bed = $room->beds()->firstOrFail();
        $patient = Patient::firstOrFail();
        $doctor = Doctor::firstOrFail();

        $admission = Admission::create([
            'admission_number' => 'INAP-TEST-0002',
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'elective',
            'status' => 'admitted',
            'admitted_at' => now()->subDays(2),
            'admitted_by' => $user->id,
        ]);

        $this->actingAs($user)->get(route('admissions.show', $admission))
            ->assertOk()
            ->assertSee($admission->admission_number);

        $this->actingAs($user)->patch(route('admissions.discharge', $admission), [
            'discharged_at' => now()->format('Y-m-d\TH:i'),
            'discharge_reason' => 'sembuh',
            'notes' => 'Kondisi membaik.',
        ])->assertSessionHasNoErrors();

        $admission->refresh();
        $this->assertEquals('discharged', $admission->status);
        $this->assertNotNull($admission->discharged_at);
        $this->assertSame($user->id, $admission->discharged_by);

        $this->assertTrue($bed->fresh()->isAvailable());
    }

    public function test_admissions_index_filters_by_status_and_date(): void
    {
        $user = $this->seedAdmin();
        $room = Room::firstOrFail();
        $bed = $room->beds()->firstOrFail();
        $patient = Patient::firstOrFail();
        $doctor = Doctor::firstOrFail();

        Admission::create([
            'admission_number' => 'INAP-TEST-0003',
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'room_id' => $room->id,
            'bed_id' => $bed->id,
            'admission_type' => 'emergency',
            'status' => 'admitted',
            'admitted_at' => now(),
        ]);

        $this->actingAs($user)->get(route('admissions.index', ['status' => 'admitted']))
            ->assertOk()
            ->assertSee($patient->name);

        $this->actingAs($user)->get(route('admissions.index', ['status' => 'discharged']))
            ->assertOk();

        $this->actingAs($user)->get(route('admissions.index', ['date' => now()->toDateString()]))
            ->assertOk()
            ->assertSee($patient->name);
    }

    public function test_registration_role_can_manage_inpatient(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();
        $this->assertTrue($registration->hasPermissionTo('manage-inpatient'));

        $this->actingAs($registration)->get(route('admissions.create'))->assertOk();
        $this->actingAs($registration)->get(route('rooms.index'))->assertOk();
        $this->actingAs($registration)->get(route('beds.index'))->assertOk();
    }

    public function test_unauthorized_user_cannot_access_inpatient(): void
    {
        $this->seed(DatabaseSeeder::class);

        $cashier = User::where('email', 'kasir@his.local')->firstOrFail();
        $this->assertFalse($cashier->hasPermissionTo('manage-inpatient'));

        $this->actingAs($cashier)->get(route('admissions.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('rooms.index'))->assertForbidden();
    }

    public function test_sidebar_shows_inpatient_menu_for_registration(): void
    {
        $this->seed(DatabaseSeeder::class);

        $registration = User::where('email', 'pendaftaran@his.local')->firstOrFail();

        $response = $this->actingAs($registration)->get(route('dashboard'));
        $response->assertOk()
            ->assertSee('Rawat Inap')
            ->assertSee('Kamar')
            ->assertSee('Tempat Tidur');
    }
}
