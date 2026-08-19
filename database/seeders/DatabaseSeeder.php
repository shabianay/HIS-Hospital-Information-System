<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Icd10;
use App\Models\Icd9Procedure;
use App\Models\LabTest;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\RadiologyTest;
use App\Models\Room;
use App\Models\Supplier;
use App\Models\Schedule;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
            'manage-master-data', 'manage-patients', 'manage-appointments',
            'manage-emr', 'manage-pharmacy', 'manage-billing',
            'manage-users', 'view-dashboard', 'manage-lab',
            'input-vital-signs', 'manage-inpatient', 'manage-radiology', 'manage-igd',
            'manage-surgery', 'manage-purchasing', 'manage-finance', 'manage-immunization', 'manage-stock-opname', 'manage-death-certificate', 'manage-bpjs', 'manage-online-registration',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $roles = ['admin', 'registration', 'doctor', 'nurse', 'pharmacist', 'cashier', 'lab_tech'];
        foreach ($roles as $role) {
            $roleObj = Role::firstOrCreate(['name' => $role]);
            if ($role === 'admin') {
                $roleObj->givePermissionTo(Permission::all());
            }
        }

        // Assign module permissions to non-admin roles
        $rolePermissionMap = [
            'registration' => ['manage-patients', 'manage-appointments', 'view-dashboard', 'manage-inpatient', 'manage-death-certificate', 'manage-online-registration'],
            'doctor' => ['manage-emr', 'view-dashboard', 'manage-surgery', 'manage-death-certificate'],
            'nurse' => ['manage-emr', 'view-dashboard', 'input-vital-signs', 'manage-inpatient', 'manage-igd', 'manage-surgery', 'manage-immunization'],
            'pharmacist' => ['manage-pharmacy', 'manage-purchasing', 'manage-stock-opname', 'view-dashboard'],
            'cashier' => ['manage-billing', 'manage-finance', 'manage-bpjs', 'view-dashboard'],
            'lab_tech' => ['manage-lab', 'manage-radiology', 'view-dashboard'],
        ];
        foreach ($rolePermissionMap as $roleName => $perms) {
            $roleObj = Role::where('name', $roleName)->first();
            if ($roleObj) {
                $roleObj->syncPermissions($perms);
            }
        }

        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@his.local'],
            [
                'name' => 'Admin HIS',
                'password' => Hash::make('password'),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Registration User
        $registration = User::firstOrCreate(
            ['email' => 'pendaftaran@his.local'],
            [
                'name' => 'Budi Staff Pendaftaran',
                'password' => Hash::make('password'),
            ]
        );
        if (! $registration->hasRole('registration')) {
            $registration->assignRole('registration');
        }

        // Nurse User
        $nurse = User::firstOrCreate(
            ['email' => 'perawat@his.local'],
            [
                'name' => 'Eka Staff Perawat',
                'password' => Hash::make('password'),
            ]
        );
        if (! $nurse->hasRole('nurse')) {
            $nurse->assignRole('nurse');
        }

        // Doctor User
        $docUser = User::firstOrCreate(
            ['email' => 'dokter@his.local'],
            [
                'name' => 'dr. Andi',
                'password' => Hash::make('password'),
            ]
        );
        if (! $docUser->hasRole('doctor')) {
            $docUser->assignRole('doctor');
        }

        // Cashier User
        $cashier = User::firstOrCreate(
            ['email' => 'kasir@his.local'],
            [
                'name' => 'Siti Staff Kasir',
                'password' => Hash::make('password'),
            ]
        );
        if (! $cashier->hasRole('cashier')) {
            $cashier->assignRole('cashier');
        }

        // Pharmacist User
        $pharmacist = User::firstOrCreate(
            ['email' => 'apoteker@his.local'],
            [
                'name' => 'Rudi Staff Apoteker',
                'password' => Hash::make('password'),
            ]
        );
        if (! $pharmacist->hasRole('pharmacist')) {
            $pharmacist->assignRole('pharmacist');
        }

        // Lab Tech User
        $labTech = User::firstOrCreate(
            ['email' => 'lab@his.local'],
            [
                'name' => 'Dewi Staff Lab',
                'password' => Hash::make('password'),
            ]
        );
        if (! $labTech->hasRole('lab_tech')) {
            $labTech->assignRole('lab_tech');
        }

        // Polis
        $polisData = [
            ['code' => 'UMU', 'name' => 'Umum', 'description' => 'Poli pemeriksaan kesehatan umum'],
            ['code' => 'ANK', 'name' => 'Anak', 'description' => 'Poli spesialis kesehatan anak'],
            ['code' => 'KDG', 'name' => 'Kandungan', 'description' => 'Poli spesialis kandungan & kebidanan'],
            ['code' => 'GGI', 'name' => 'Gigi', 'description' => 'Poli spesialis gigi & mulut'],
            ['code' => 'PDL', 'name' => 'Penyakit Dalam', 'description' => 'Poli spesialis penyakit dalam'],
        ];
        $poliModels = [];
        foreach ($polisData as $p) {
            $poliModels[] = Poli::firstOrCreate(['name' => $p['name']], [
                'code' => $p['code'],
                'description' => $p['description'],
            ]);
        }

        // Doctors
        $doctorsData = [
            ['name' => 'dr. Andi, Sp.PD', 'specialization' => 'Penyakit Dalam', 'license_number' => 'SIP/001/PD/2026'],
            ['name' => 'dr. Budi, Sp.A', 'specialization' => 'Anak', 'license_number' => 'SIP/002/A/2026'],
            ['name' => 'dr. Clara, Sp.OG', 'specialization' => 'Kandungan', 'license_number' => 'SIP/003/OG/2026'],
        ];
        $doctorModels = [];
        foreach ($doctorsData as $d) {
            $doctorModels[] = Doctor::firstOrCreate(['license_number' => $d['license_number']], [
                'name' => $d['name'],
                'specialization' => $d['specialization'],
            ]);
        }

        // Link doctor user to the first doctor record
        $docUser = User::where('email', 'dokter@his.local')->first();
        if ($docUser && isset($doctorModels[0]) && ! $doctorModels[0]->user_id) {
            $doctorModels[0]->update(['user_id' => $docUser->id]);
        }

        // Schedules
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
        foreach ($doctorModels as $index => $doc) {
            // Assign doctor to a specific poli
            $poli = $poliModels[$index % count($poliModels)];
            foreach ($days as $day) {
                Schedule::firstOrCreate(
                    [
                        'doctor_id' => $doc->id,
                        'poli_id' => $poli->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '08:00',
                        'end_time' => '12:00',
                        'daily_quota' => 20,
                        'consultation_fee' => 100000 + ($index * 25000),
                        'is_active' => true,
                    ]
                );
            }
        }

        // Patients
        $patientsData = [
            ['nik' => '3171010101900001', 'name' => 'Ahmad Rian', 'date_of_birth' => '1990-05-15', 'gender' => 'L', 'address' => 'Jl. Merdeka No. 10', 'phone_number' => '081234567890'],
            ['nik' => '3171010101950002', 'name' => 'Siti Aminah', 'date_of_birth' => '1995-10-22', 'gender' => 'P', 'address' => 'Jl. Sudirman No. 5', 'phone_number' => '087876543210'],
            ['nik' => '3171010101880003', 'name' => 'Joko Widodo', 'date_of_birth' => '1988-02-10', 'gender' => 'L', 'address' => 'Jl. Diponegoro No. 8', 'phone_number' => '082199887766'],
            ['nik' => '3171010101920004', 'name' => 'Rina Wijaya', 'date_of_birth' => '1992-12-01', 'gender' => 'P', 'address' => 'Jl. Gajah Mada No. 12', 'phone_number' => '085611223344'],
            ['nik' => '3171010101800005', 'name' => 'Bambang Hartono', 'date_of_birth' => '1980-07-20', 'gender' => 'L', 'address' => 'Jl. Kartini No. 4', 'phone_number' => '081122334455'],
        ];
        $patientModels = [];
        foreach ($patientsData as $pat) {
            $patientModels[] = Patient::firstOrCreate(['nik' => $pat['nik']], $pat);
        }

        // Medicines
        $medicinesData = [
            ['name' => 'Paracetamol 500mg', 'generic_name' => 'Paracetamol', 'category' => 'Analgesik', 'unit' => 'Tablet', 'buy_price' => 500.00, 'sell_price' => 1000.00, 'minimum_stock' => 50],
            ['name' => 'Amoxicillin 500mg', 'generic_name' => 'Amoxicillin', 'category' => 'Antibiotik', 'unit' => 'Tablet', 'buy_price' => 1200.00, 'sell_price' => 2000.00, 'minimum_stock' => 50],
            ['name' => 'Ibuprofen 400mg', 'generic_name' => 'Ibuprofen', 'category' => 'NSAID', 'unit' => 'Tablet', 'buy_price' => 800.00, 'sell_price' => 1500.00, 'minimum_stock' => 50],
            ['name' => 'Cough Syrup', 'generic_name' => 'Guaifenesin', 'category' => 'Antitusif', 'unit' => 'Botol', 'buy_price' => 5000.00, 'sell_price' => 7500.00, 'minimum_stock' => 10],
            ['name' => 'Amlodipine 5mg', 'generic_name' => 'Amlodipine', 'category' => 'Antihipertensi', 'unit' => 'Tablet', 'buy_price' => 300.00, 'sell_price' => 600.00, 'minimum_stock' => 50],
            ['name' => 'Metformin 500mg', 'generic_name' => 'Metformin', 'category' => 'Antidiabetes', 'unit' => 'Tablet', 'buy_price' => 400.00, 'sell_price' => 800.00, 'minimum_stock' => 50],
        ];
        foreach ($medicinesData as $med) {
            $medicine = Medicine::firstOrCreate(['name' => $med['name']], $med);
            // Create initial stock
            MedicineStock::firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => 'BATCH-'.rand(100, 999),
                ],
                [
                    'quantity' => 100,
                    'expiry_date' => now()->addYears(2)->toDateString(),
                ]
            );
        }

        // Tariffs
        $tariffsData = [
            ['name' => 'Pemeriksaan Dokter Umum', 'type' => 'konsultasi', 'price' => 50000.00],
            ['name' => 'Pemeriksaan Dokter Spesialis', 'type' => 'konsultasi', 'price' => 100000.00],
            ['name' => 'Nebulizer', 'type' => 'tindakan', 'price' => 75000.00],
            ['name' => 'EKG (Rekam Jantung)', 'type' => 'tindakan', 'price' => 125000.00],
            ['name' => 'Rawat Luka Ringan', 'type' => 'tindakan', 'price' => 40000.00],
        ];
        foreach ($tariffsData as $tar) {
            Tariff::firstOrCreate(['name' => $tar['name']], $tar);
        }

        // ICD-10
        $icdData = [
            ['code' => 'A09', 'description' => 'Diarrhoea and gastroenteritis of infectious origin'],
            ['code' => 'I10', 'description' => 'Essential (primary) hypertension'],
            ['code' => 'E11', 'description' => 'Non-insulin-dependent diabetes mellitus'],
            ['code' => 'J00', 'description' => 'Acute nasopharyngitis [common cold]'],
            ['code' => 'K30', 'description' => 'Dyspepsia'],
            ['code' => 'M79.1', 'description' => 'Myalgia'],
            ['code' => 'R51', 'description' => 'Headache'],
            ['code' => 'R50.9', 'description' => 'Fever, unspecified'],
            ['code' => 'H10.9', 'description' => 'Conjunctivitis, unspecified'],
            ['code' => 'L23.9', 'description' => 'Allergic contact dermatitis, unspecified'],
        ];
        foreach ($icdData as $icd) {
            Icd10::firstOrCreate(['code' => $icd['code']], ['description' => $icd['description']]);
        }

        // ICD-9-CM Procedures
        $icd9Data = [
            ['code' => '03.31', 'name' => 'Spinal Tap (Pungsi Lumbal)', 'category' => 'Diagnostik'],
            ['code' => '34.22', 'name' => 'Thoracoscopy (Biopsi Pleura)', 'category' => 'Diagnostik'],
            ['code' => '38.91', 'name' => 'Arterial Catheterization', 'category' => 'Kardiologi'],
            ['code' => '39.95', 'name' => 'Hemodialysis', 'category' => 'Terapi'],
            ['code' => '45.13', 'name' => 'Esophagogastroduodenoscopy (EGD)', 'category' => 'Diagnostik'],
            ['code' => '47.09', 'name' => 'Appendectomy (Surgical)', 'category' => 'Bedah'],
            ['code' => '51.23', 'name' => 'Laparoscopic Cholecystectomy', 'category' => 'Bedah'],
            ['code' => '54.21', 'name' => 'Laparoscopy', 'category' => 'Diagnostik'],
            ['code' => '87.44', 'name' => 'Routine Chest X-Ray', 'category' => 'Radiologi'],
            ['code' => '88.72', 'name' => 'Diagnostic Ultrasound of Heart (Echocardiogram)', 'category' => 'Kardiologi'],
        ];
        foreach ($icd9Data as $proc) {
            Icd9Procedure::firstOrCreate(['code' => $proc['code']], $proc);
        }

        // Lab Tests
        $labTestsData = [
            ['name' => 'Hemoglobin (Hb)', 'category' => 'Hematologi', 'unit' => 'g/dL', 'reference_range' => '13.0 - 17.5', 'price' => 25000],
            ['name' => 'Leukosit', 'category' => 'Hematologi', 'unit' => '10³/µL', 'reference_range' => '4.5 - 11.0', 'price' => 25000],
            ['name' => 'Trombosit', 'category' => 'Hematologi', 'unit' => '10³/µL', 'reference_range' => '150 - 440', 'price' => 25000],
            ['name' => 'Hematokrit', 'category' => 'Hematologi', 'unit' => '%', 'reference_range' => '40 - 52', 'price' => 20000],
            ['name' => 'Glukosa Darah Puasa', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '70 - 100', 'price' => 35000],
            ['name' => 'Kolesterol Total', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '< 200', 'price' => 40000],
            ['name' => 'Trigiserida', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '< 150', 'price' => 40000],
            ['name' => 'Asam Urat', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '3.5 - 7.2', 'price' => 35000],
            ['name' => 'Golongan Darah', 'category' => 'Imunoserologi', 'unit' => '-', 'reference_range' => 'A/B/AB/O', 'price' => 20000],
            ['name' => 'Urine Lengkap', 'category' => 'Urinalisis', 'unit' => '-', 'reference_range' => '-', 'price' => 30000],
        ];
        foreach ($labTestsData as $test) {
            LabTest::firstOrCreate(['name' => $test['name']], $test);
        }

        // Radiology Tests
        $radiologyTestsData = [
            ['name' => 'Foto Thorax PA', 'category' => 'Rontgen', 'unit' => 'proyeksi', 'reference_range' => 'Inspirasi optimal, tanpa kelainan', 'price' => 100000],
            ['name' => 'Foto Thorax 3 Posisi', 'category' => 'Rontgen', 'unit' => 'proyeksi', 'reference_range' => 'PA, Lateral, Oblique', 'price' => 150000],
            ['name' => 'Foto Ekstremitas', 'category' => 'Rontgen', 'unit' => 'proyeksi', 'reference_range' => 'Sesuai indikasi klinis', 'price' => 80000],
            ['name' => 'USG Abdomen', 'category' => 'USG', 'unit' => 'sesi', 'reference_range' => 'Puasa 6 jam', 'price' => 250000],
            ['name' => 'USG Obstetri', 'category' => 'USG', 'unit' => 'sesi', 'reference_range' => 'Kandung kemih penuh', 'price' => 200000],
            ['name' => 'USG Payudara', 'category' => 'USG', 'unit' => 'sesi', 'reference_range' => '-', 'price' => 180000],
            ['name' => 'CT Scan Kepala', 'category' => 'CT Scan', 'unit' => 'sesi', 'reference_range' => 'Tanpa kontras', 'price' => 900000],
            ['name' => 'CT Scan Thorax', 'category' => 'CT Scan', 'unit' => 'sesi', 'reference_range' => '-', 'price' => 1100000],
            ['name' => 'MRI Kepala', 'category' => 'MRI', 'unit' => 'sesi', 'reference_range' => 'Kontraindikasi alat logam', 'price' => 1800000],
            ['name' => 'EKG (Rekam Jantung)', 'category' => 'Kardiologi', 'unit' => 'pemeriksaan', 'reference_range' => '-', 'price' => 125000],
        ];
        foreach ($radiologyTestsData as $test) {
            RadiologyTest::firstOrCreate(['name' => $test['name']], $test);
        }

        // Suppliers
        $suppliersData = [
            ['name' => 'PT Kimia Farma Tbk', 'contact_person' => 'Bpk. Agus', 'phone' => '021-5550123', 'email' => 'sales@kimiafarma.co.id', 'address' => 'Jl. Kunir No. 15, Jakarta Pusat'],
            ['name' => 'PT Indofarma Tbk', 'contact_person' => 'Ibu Ratna', 'phone' => '022-5550456', 'email' => 'sales@indofarma.co.id', 'address' => 'Jl. Soekarno Hatta No. 20, Bandung'],
            ['name' => 'CV Sumber Sehat', 'contact_person' => 'Bpk. Hendra', 'phone' => '031-5550789', 'email' => 'cs@sumbersehat.co.id', 'address' => 'Jl. Raya Darmo No. 8, Surabaya'],
        ];
        foreach ($suppliersData as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }

        // Generate a sample appointment
        $schedule = Schedule::first();
        if ($schedule) {
            Appointment::firstOrCreate(
                [
                    'patient_id' => $patientModels[0]->id,
                    'appointment_date' => now()->toDateString(),
                ],
                [
                    'queue_number' => 'QUMU-20260808-01',
                    'doctor_id' => $schedule->doctor_id,
                    'poli_id' => $schedule->poli_id,
                    'schedule_id' => $schedule->id,
                    'status' => 'waiting',
                    'consultation_fee' => $schedule->consultation_fee,
                    'notes' => 'Pemeriksaan rutin keluhan demam',
                ]
            );
        }

        // Rooms & Beds (Rawat Inap)
        $roomsData = [
            ['code' => 'VIP-01', 'name' => 'Kamar VIP 1', 'room_type' => 'vip', 'price_per_day' => 750000, 'description' => 'Kamar VIP dengan fasilitas lengkap'],
            ['code' => 'K1-01', 'name' => 'Kelas 1 - Ruang A', 'room_type' => 'class_1', 'price_per_day' => 400000, 'description' => 'Kelas 1 dengan 2 tempat tidur'],
            ['code' => 'K2-01', 'name' => 'Kelas 2 - Ruang B', 'room_type' => 'class_2', 'price_per_day' => 250000, 'description' => 'Kelas 2 dengan 4 tempat tidur'],
            ['code' => 'K3-01', 'name' => 'Kelas 3 - Ruang C', 'room_type' => 'class_3', 'price_per_day' => 150000, 'description' => 'Kelas 3 dengan 6 tempat tidur'],
            ['code' => 'ICU-01', 'name' => 'ICU', 'room_type' => 'icu', 'price_per_day' => 1500000, 'description' => 'Intensive Care Unit'],
        ];
        $roomModels = [];
        foreach ($roomsData as $r) {
            $room = Room::firstOrCreate(['code' => $r['code']], $r);
            $roomModels[$room->code] = $room;
        }

        $bedsData = [
            'VIP-01' => ['01', '02'],
            'K1-01' => ['01', '02'],
            'K2-01' => ['01', '02', '03', '04'],
            'K3-01' => ['01', '02', '03', '04', '05', '06'],
            'ICU-01' => ['01', '02'],
        ];
        foreach ($bedsData as $roomCode => $bedNumbers) {
            if (! isset($roomModels[$roomCode])) {
                continue;
            }
            foreach ($bedNumbers as $num) {
                Bed::firstOrCreate(
                    ['room_id' => $roomModels[$roomCode]->id, 'bed_number' => $num],
                    ['is_active' => true]
                );
            }
        }
    }
}
