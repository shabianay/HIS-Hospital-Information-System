<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Billing;
use App\Models\BillingItem;
use App\Models\BillingPayment;
use App\Models\BpjsClaim;
use App\Models\DeathCertificate;
use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\EmergencyVisit;
use App\Models\Expense;
use App\Models\Icd10;
use App\Models\Icd9Procedure;
use App\Models\Immunization;
use App\Models\LabRequest;
use App\Models\LabRequestItem;
use App\Models\LabTest;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\MedicineStock;
use App\Models\OnlineRegistration;
use App\Models\Patient;
use App\Models\Poli;
use App\Models\Prescription;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RadiologyRequest;
use App\Models\RadiologyRequestItem;
use App\Models\RadiologyTest;
use App\Models\Refund;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\SepRecord;
use App\Models\StockMutation;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Supplier;
use App\Models\Surgery;
use App\Models\Tariff;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
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

        // Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@his.local'],
            ['name' => 'Admin HIS', 'password' => Hash::make('password')]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        $registration = User::firstOrCreate(
            ['email' => 'pendaftaran@his.local'],
            ['name' => 'Budi Staff Pendaftaran', 'password' => Hash::make('password')]
        );
        if (! $registration->hasRole('registration')) {
            $registration->assignRole('registration');
        }

        $nurse = User::firstOrCreate(
            ['email' => 'perawat@his.local'],
            ['name' => 'Eka Staff Perawat', 'password' => Hash::make('password')]
        );
        if (! $nurse->hasRole('nurse')) {
            $nurse->assignRole('nurse');
        }

        $docUser = User::firstOrCreate(
            ['email' => 'dokter@his.local'],
            ['name' => 'dr. Andi', 'password' => Hash::make('password')]
        );
        if (! $docUser->hasRole('doctor')) {
            $docUser->assignRole('doctor');
        }

        $cashier = User::firstOrCreate(
            ['email' => 'kasir@his.local'],
            ['name' => 'Siti Staff Kasir', 'password' => Hash::make('password')]
        );
        if (! $cashier->hasRole('cashier')) {
            $cashier->assignRole('cashier');
        }

        $pharmacist = User::firstOrCreate(
            ['email' => 'apoteker@his.local'],
            ['name' => 'Rudi Staff Apoteker', 'password' => Hash::make('password')]
        );
        if (! $pharmacist->hasRole('pharmacist')) {
            $pharmacist->assignRole('pharmacist');
        }

        $labTech = User::firstOrCreate(
            ['email' => 'lab@his.local'],
            ['name' => 'Dewi Staff Lab', 'password' => Hash::make('password')]
        );
        if (! $labTech->hasRole('lab_tech')) {
            $labTech->assignRole('lab_tech');
        }

        $users = [
            'admin' => $admin,
            'registration' => $registration,
            'nurse' => $nurse,
            'doctor' => $docUser,
            'cashier' => $cashier,
            'pharmacist' => $pharmacist,
            'lab_tech' => $labTech,
        ];

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
        $poliByName = collect($poliModels)->keyBy('name');

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
            ['nik' => '3171010101900001', 'rm_number' => 'RM-2026-00001', 'name' => 'Ahmad Rian', 'date_of_birth' => '1990-05-15', 'gender' => 'L', 'address' => 'Jl. Merdeka No. 10', 'phone_number' => '081234567890', 'insurance_provider' => 'bpjs', 'insurance_number' => '0001234567890001', 'allergies' => 'Penisilin', 'chronic_conditions' => 'Hipertensi'],
            ['nik' => '3171010101950002', 'rm_number' => 'RM-2026-00002', 'name' => 'Siti Aminah', 'date_of_birth' => '1995-10-22', 'gender' => 'P', 'address' => 'Jl. Sudirman No. 5', 'phone_number' => '087876543210', 'insurance_provider' => null, 'insurance_number' => null, 'allergies' => null, 'chronic_conditions' => null],
            ['nik' => '3171010101880003', 'rm_number' => 'RM-2026-00003', 'name' => 'Joko Widodo', 'date_of_birth' => '1988-02-10', 'gender' => 'L', 'address' => 'Jl. Diponegoro No. 8', 'phone_number' => '082199887766', 'insurance_provider' => 'bpjs', 'insurance_number' => '0005555555555555', 'allergies' => null, 'chronic_conditions' => 'Diabetes Melitus Tipe 2, Hipertensi'],
            ['nik' => '3171010101920004', 'rm_number' => 'RM-2026-00004', 'name' => 'Rina Wijaya', 'date_of_birth' => '1992-12-01', 'gender' => 'P', 'address' => 'Jl. Gajah Mada No. 12', 'phone_number' => '085611223344', 'insurance_provider' => null, 'insurance_number' => null, 'allergies' => null, 'chronic_conditions' => null],
            ['nik' => '3171010101800005', 'rm_number' => 'RM-2026-00005', 'name' => 'Bambang Hartono', 'date_of_birth' => '1980-07-20', 'gender' => 'L', 'address' => 'Jl. Kartini No. 4', 'phone_number' => '081122334455', 'insurance_provider' => null, 'insurance_number' => null, 'allergies' => 'Sulfa', 'chronic_conditions' => null],
            ['nik' => '3171010119000006', 'rm_number' => 'RM-2026-00006', 'name' => 'Naura Salsabila', 'date_of_birth' => '2019-03-10', 'gender' => 'P', 'address' => 'Jl. Merdeka No. 10', 'phone_number' => '081234567890', 'insurance_provider' => 'bpjs', 'insurance_number' => '0001234567890123', 'allergies' => null, 'chronic_conditions' => null],
            ['nik' => '3171010145000007', 'rm_number' => 'RM-2026-00007', 'name' => 'Hasan Basri', 'date_of_birth' => '1945-11-30', 'gender' => 'L', 'address' => 'Jl. Melati No. 2', 'phone_number' => '081312345678', 'insurance_provider' => 'bpjs', 'insurance_number' => '0007777777777777', 'allergies' => null, 'chronic_conditions' => 'Hipertensi, Penyakit Jantung Koroner'],
        ];
        $patientModels = [];
        foreach ($patientsData as $pat) {
            $patientModels[] = Patient::updateOrCreate(['nik' => $pat['nik']], $pat);
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
        $medicineModels = [];
        foreach ($medicinesData as $med) {
            $medicine = Medicine::firstOrCreate(['name' => $med['name']], $med);
            $medicineModels[$med['name']] = $medicine;
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
        $icd9Models = [];
        foreach ($icd9Data as $proc) {
            $icd9Models[$proc['code']] = Icd9Procedure::firstOrCreate(['code' => $proc['code']], $proc);
        }

        // Lab Tests
        $labTestsData = [
            ['name' => 'Hemoglobin (Hb)', 'category' => 'Hematologi', 'unit' => 'g/dL', 'reference_range' => '13.0 - 17.5', 'price' => 25000],
            ['name' => 'Leukosit', 'category' => 'Hematologi', 'unit' => '10³/µL', 'reference_range' => '4.5 - 11.0', 'price' => 25000],
            ['name' => 'Trombosit', 'category' => 'Hematologi', 'unit' => '10³/µL', 'reference_range' => '150 - 440', 'price' => 25000],
            ['name' => 'Hematokrit', 'category' => 'Hematologi', 'unit' => '%', 'reference_range' => '40 - 52', 'price' => 20000],
            ['name' => 'Glukosa Darah Puasa', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '70 - 100', 'price' => 35000],
            ['name' => 'Kolesterol Total', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '< 200', 'price' => 40000],
            ['name' => 'Trigliserida', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '< 150', 'price' => 40000],
            ['name' => 'Asam Urat', 'category' => 'Kimia Klinik', 'unit' => 'mg/dL', 'reference_range' => '3.5 - 7.2', 'price' => 35000],
            ['name' => 'Golongan Darah', 'category' => 'Imunoserologi', 'unit' => '-', 'reference_range' => 'A/B/AB/O', 'price' => 20000],
            ['name' => 'Urine Lengkap', 'category' => 'Urinalisis', 'unit' => '-', 'reference_range' => '-', 'price' => 30000],
        ];
        $labTestModels = [];
        foreach ($labTestsData as $test) {
            $labTestModels[$test['name']] = LabTest::firstOrCreate(['name' => $test['name']], $test);
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
        $radiologyTestModels = [];
        foreach ($radiologyTestsData as $test) {
            $radiologyTestModels[$test['name']] = RadiologyTest::firstOrCreate(['name' => $test['name']], $test);
        }

        // Suppliers
        $suppliersData = [
            ['name' => 'PT Kimia Farma Tbk', 'contact_person' => 'Bpk. Agus', 'phone' => '021-5550123', 'email' => 'sales@kimiafarma.co.id', 'address' => 'Jl. Kunir No. 15, Jakarta Pusat'],
            ['name' => 'PT Indofarma Tbk', 'contact_person' => 'Ibu Ratna', 'phone' => '022-5550456', 'email' => 'sales@indofarma.co.id', 'address' => 'Jl. Soekarno Hatta No. 20, Bandung'],
            ['name' => 'CV Sumber Sehat', 'contact_person' => 'Bpk. Hendra', 'phone' => '031-5550789', 'email' => 'cs@sumbersehat.co.id', 'address' => 'Jl. Raya Darmo No. 8, Surabaya'],
        ];
        $supplierModels = [];
        foreach ($suppliersData as $supplier) {
            $supplierModels[] = Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
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
        $bedModels = [];
        foreach ($bedsData as $roomCode => $bedNumbers) {
            if (! isset($roomModels[$roomCode])) {
                continue;
            }
            foreach ($bedNumbers as $num) {
                $bed = Bed::firstOrCreate(
                    ['room_id' => $roomModels[$roomCode]->id, 'bed_number' => $num],
                    ['is_active' => true]
                );
                $bedModels[$roomCode.'-'.$num] = $bed;
            }
        }

        // ---------------------------------------------------------------------
        // Generate a sample appointment
        // ---------------------------------------------------------------------
        $schedule = Schedule::first();
        if ($schedule) {
            Appointment::firstOrCreate(
                ['patient_id' => $patientModels[0]->id, 'appointment_date' => now()->toDateString()],
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

        // ---------------------------------------------------------------------
        // Data demo lengkap (alur klinis, billing, rawat inap, IGD, OK,
        // purchasing, keuangan, imunisasi, opname, surat kematian, BPJS, dan
        // antrean online) hanya dibuat di lingkungan non-testing agar baseline
        // data yang diharapkan oleh test suite tidak berubah.
        // ---------------------------------------------------------------------
        if (app()->environment('testing')) {
            return;
        }

        // ---------------------------------------------------------------------
        // Clinical flow: Appointments -> Medical Records -> Labs/Radiology
        // ---------------------------------------------------------------------
        $dayNames = [
            'Monday' => 'senin', 'Tuesday' => 'selasa', 'Wednesday' => 'rabu',
            'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu', 'Sunday' => 'minggu',
        ];
        $scheduleFor = function (Carbon $date) use ($dayNames) {
            return Schedule::where('day_of_week', $dayNames[$date->format('l')])->first() ?? Schedule::first();
        };
        $dateOf = fn (int $n) => Carbon::create(2026, 8, 19)->subDays($n);
        $dateStr = fn (int $n) => $dateOf($n)->toDateString();

        $makeAppointment = function (int $patientIdx, int $daysBack, string $status, string $queue, string $notes) use ($patientModels, $scheduleFor, $dateOf, $dateStr, &$appointments) {
            $schedule = $scheduleFor($dateOf($daysBack));
            $appointment = Appointment::updateOrCreate(
                ['patient_id' => $patientModels[$patientIdx]->id, 'appointment_date' => $dateStr($daysBack)],
                [
                    'queue_number' => $queue,
                    'doctor_id' => $schedule->doctor_id,
                    'poli_id' => $schedule->poli_id,
                    'schedule_id' => $schedule->id,
                    'status' => $status,
                    'consultation_fee' => $schedule->consultation_fee,
                    'notes' => $notes,
                ]
            );
            $appointments[$queue] = $appointment;

            return $appointment;
        };

        $appointments = [];
        $makeAppointment(0, 0, 'waiting', 'QUMU-20260819-001', 'Demam & batuk 2 hari');
        $makeAppointment(5, 0, 'waiting', 'QANK-20260819-001', 'Imunisasi dasar lanjutan');
        $makeAppointment(1, 0, 'waiting', 'QUMU-20260819-002', 'Cek-up kesehatan umum');
        $makeAppointment(2, 2, 'completed', 'QPDL-20260817-001', 'Kontrol DM & hipertensi');
        $makeAppointment(3, 1, 'completed', 'QKDG-20260818-001', 'Kontrol kehamilan trimester II');
        $makeAppointment(4, 3, 'completed', 'QUMU-20260816-001', 'Demam, nyeri badan');
        $makeAppointment(2, 4, 'completed', 'QUMU-20260815-001', 'Kontrol dispepsia');
        $makeAppointment(5, 6, 'completed', 'QANK-20260813-001', 'Imunisasi dasar');
        $makeAppointment(3, 5, 'cancelled', 'QKDG-20260814-001', 'Janji kontrol, batal');
        $makeAppointment(0, 7, 'completed', 'QUMU-20260812-001', 'Cek tekanan darah');

        // Medical records + vitals + diagnoses + prescriptions
        $medicalRecord = function (Appointment $appointment, array $soap, array $vitals, array $diagnoses = [], array $prescriptions = []) use ($users) {
            $record = MedicalRecord::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'subjective' => $soap['subjective'],
                    'objective' => $soap['objective'],
                    'assessment' => $soap['assessment'],
                    'plan' => $soap['plan'],
                    'chief_complaint' => $soap['chief_complaint'],
                    'allergy_notes' => $soap['allergy_notes'] ?? null,
                    'status' => 'finalized',
                ]
            );

            VitalSign::updateOrCreate(
                ['appointment_id' => $appointment->id],
                array_merge($vitals, ['recorded_by' => $users['nurse']->id])
            );

            foreach ($diagnoses as $diag) {
                Diagnosis::firstOrCreate(
                    ['medical_record_id' => $record->id, 'icd_code' => $diag['icd_code']],
                    [
                        'description' => $diag['description'],
                        'is_primary' => $diag['is_primary'],
                    ]
                );
            }

            foreach ($prescriptions as $rx) {
                Prescription::firstOrCreate(
                    ['medical_record_id' => $record->id, 'medicine_id' => $rx['medicine_id']],
                    [
                        'quantity' => $rx['quantity'],
                        'dosage' => $rx['dosage'],
                        'frequency' => $rx['frequency'],
                        'duration' => $rx['duration'],
                        'instructions' => $rx['instructions'],
                        'is_dispensed' => true,
                    ]
                );
                StockMutation::firstOrCreate(
                    [
                        'medicine_id' => $rx['medicine_id'],
                        'type' => 'out',
                        'quantity' => $rx['quantity'],
                        'reference' => 'APPT-'.$appointment->queue_number,
                    ],
                    ['notes' => 'Penyerahan resep']
                );
            }

            return $record;
        };

        // appt 3: p2 Joko, PDL control (DM + HTN)
        $mr3 = $medicalRecord(
            $appointments['QPDL-20260817-001'],
            [
                'chief_complaint' => 'Kontrol rutin DM & hipertensi',
                'subjective' => 'Pasien kontrol rutin, riwayat DM tipe 2 dan hipertensi sejak 5 tahun. Tidak ada keluhan baru.',
                'objective' => 'Kesadaran kompos mentis, tanda vital stabil, tidak tampak sesak.',
                'assessment' => 'DM tipe 2 terkontrol, hipertensi derajat 1',
                'plan' => 'Lanjutkan obat, evaluasi profil glukosa & lipid, kontrol 2 minggu lagi.',
            ],
            ['temperature' => 36.5, 'blood_pressure_systolic' => 140, 'blood_pressure_diastolic' => 85, 'heart_rate' => 82, 'respiratory_rate' => 20, 'weight' => 72, 'height' => 165, 'oxygen_saturation' => 98],
            [
                ['icd_code' => 'I10', 'description' => 'Essential (primary) hypertension', 'is_primary' => true],
                ['icd_code' => 'E11', 'description' => 'Non-insulin-dependent diabetes mellitus', 'is_primary' => false],
            ],
            [
                ['medicine_id' => $medicineModels['Amlodipine 5mg']->id, 'quantity' => 30, 'dosage' => '1 tablet', 'frequency' => '1x sehari', 'duration' => '30 hari', 'instructions' => 'Dikonsumsi pagi hari'],
                ['medicine_id' => $medicineModels['Metformin 500mg']->id, 'quantity' => 60, 'dosage' => '1 tablet', 'frequency' => '2x sehari', 'duration' => '30 hari', 'instructions' => 'Setelah makan'],
            ]
        );

        // appt 4: p3 Rina, Kandungan control
        $mr4 = $medicalRecord(
            $appointments['QKDG-20260818-001'],
            [
                'chief_complaint' => 'Kontrol kehamilan trimester II',
                'subjective' => 'Kehamilan 22 minggu, pergerakan janin dirasakan aktif, tidak ada perdarahan.',
                'objective' => 'Keadaan umum baik, konjungtiva tidak anemis.',
                'assessment' => 'Kehamilan 22 minggu normal, kondisi ibu & janin baik',
                'plan' => 'Jadwalkan USG obstetri, kontrol 4 minggu lagi.',
            ],
            ['temperature' => 36.7, 'blood_pressure_systolic' => 110, 'blood_pressure_diastolic' => 70, 'heart_rate' => 88, 'respiratory_rate' => 20, 'weight' => 58, 'height' => 158, 'oxygen_saturation' => 99]
        );

        // appt 5: p4 Bambang, Umum (viral infection)
        $mr5 = $medicalRecord(
            $appointments['QUMU-20260816-001'],
            [
                'chief_complaint' => 'Demam, nyeri badan',
                'subjective' => 'Demam sejak 2 hari, nyeri seluruh badan, batuk kering.',
                'objective' => 'Suhu 38.2C, faring hiperemis ringan.',
                'assessment' => 'Infeksi saluran pernapasan akut / myalgia',
                'plan' => 'Istirahat cukup, minum banyak cairan, obat pereda nyeri & demam. Kontrol bila demam menetap > 3 hari.',
            ],
            ['temperature' => 38.2, 'blood_pressure_systolic' => 120, 'blood_pressure_diastolic' => 80, 'heart_rate' => 96, 'respiratory_rate' => 22, 'weight' => 68, 'height' => 170, 'oxygen_saturation' => 97],
            [
                ['icd_code' => 'J00', 'description' => 'Acute nasopharyngitis [common cold]', 'is_primary' => true],
                ['icd_code' => 'M79.1', 'description' => 'Myalgia', 'is_primary' => false],
            ],
            [
                ['medicine_id' => $medicineModels['Paracetamol 500mg']->id, 'quantity' => 12, 'dosage' => '1 tablet', 'frequency' => '3x sehari', 'duration' => '4 hari', 'instructions' => 'Setelah makan'],
                ['medicine_id' => $medicineModels['Ibuprofen 400mg']->id, 'quantity' => 10, 'dosage' => '1 tablet', 'frequency' => '2x sehari', 'duration' => '5 hari', 'instructions' => 'Setelah makan, jangan saat perut kosong'],
                ['medicine_id' => $medicineModels['Cough Syrup']->id, 'quantity' => 1, 'dosage' => '1 sendok takar', 'frequency' => '3x sehari', 'duration' => '5 hari', 'instructions' => 'Dikocok dahulu'],
            ]
        );

        // appt 6: p2 Joko, dyspepsia
        $mr6 = $medicalRecord(
            $appointments['QUMU-20260815-001'],
            [
                'chief_complaint' => 'Nyeri ulu hati',
                'subjective' => 'Nyeri ulu hati sejak 1 minggu, terutama setelah makan pedas.',
                'objective' => 'Tidak tampak nyeri tekan hebat, bising usus normal.',
                'assessment' => 'Dyspepsia fungsional, hiperkolesterolemia & hiperurisemia',
                'plan' => 'Perbaiki pola makan, kurangi makanan berlemak, evaluasi profil lipid.',
            ],
            ['temperature' => 36.6, 'blood_pressure_systolic' => 135, 'blood_pressure_diastolic' => 85, 'heart_rate' => 84, 'respiratory_rate' => 20, 'weight' => 74, 'height' => 165, 'oxygen_saturation' => 98],
            [
                ['icd_code' => 'K30', 'description' => 'Dyspepsia', 'is_primary' => true],
            ]
        );

        // appt 7: p5 Naura, child immunization
        $mr7 = $medicalRecord(
            $appointments['QANK-20260813-001'],
            [
                'chief_complaint' => 'Imunisasi dasar',
                'subjective' => 'Datang untuk imunisasi dasar lanjutan, tidak ada demam.',
                'objective' => 'Status gizi baik, perkembangan sesuai usia.',
                'assessment' => 'Imunisasi dasar lengkap, perkembangan normal',
                'plan' => 'Lanjut imunisasi sesuai jadwal, kontrol berkala ke poli anak.',
            ],
            ['temperature' => 36.8, 'blood_pressure_systolic' => null, 'blood_pressure_diastolic' => null, 'heart_rate' => 110, 'respiratory_rate' => 24, 'weight' => 14, 'height' => 95, 'oxygen_saturation' => 99]
        );

        // appt 9: p0 Ahmad, hypertension check-up
        $mr9 = $medicalRecord(
            $appointments['QUMU-20260812-001'],
            [
                'chief_complaint' => 'Cek tekanan darah',
                'subjective' => 'Riwayat hipertensi, kontrol rutin, kadang pusing saat stres.',
                'objective' => 'Tekanan darah tinggi pada pemeriksaan.',
                'assessment' => 'Hipertensi derajat 2',
                'plan' => 'Amlodipine 1x sehari, diet rendah garam, olahraga teratur, kontrol 2 minggu.',
            ],
            ['temperature' => 36.4, 'blood_pressure_systolic' => 155, 'blood_pressure_diastolic' => 95, 'heart_rate' => 88, 'respiratory_rate' => 20, 'weight' => 70, 'height' => 168, 'oxygen_saturation' => 98],
            [
                ['icd_code' => 'I10', 'description' => 'Essential (primary) hypertension', 'is_primary' => true],
            ],
            [
                ['medicine_id' => $medicineModels['Amlodipine 5mg']->id, 'quantity' => 30, 'dosage' => '1 tablet', 'frequency' => '1x sehari', 'duration' => '30 hari', 'instructions' => 'Pagi hari sebelum makan'],
            ]
        );

        // Lab requests
        $labRequest = function (Appointment $appointment, ?MedicalRecord $record, string $status, array $items, ?Carbon $completedAt = null, bool $urgent = false) use ($users) {
            $request = LabRequest::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'medical_record_id' => $record?->id,
                    'created_by' => $users['doctor']->id,
                    'is_urgent' => $urgent,
                    'status' => $status,
                    'completed_at' => $completedAt,
                ]
            );
            foreach ($items as $item) {
                $test = $item['test'];
                LabRequestItem::firstOrCreate(
                    ['lab_request_id' => $request->id, 'lab_test_id' => $test->id],
                    [
                        'test_name' => $test->name,
                        'unit' => $test->unit,
                        'reference_range' => $test->reference_range,
                        'price' => $test->price,
                        'result_value' => $item['result_value'] ?? null,
                        'result_status' => $item['result_status'] ?? 'pending',
                        'result_notes' => $item['result_notes'] ?? null,
                    ]
                );
            }

            return $request;
        };

        $labRequest($appointments['QPDL-20260817-001'], $mr3, 'completed', [
            ['test' => $labTestModels['Hemoglobin (Hb)'], 'result_value' => '14.2', 'result_status' => 'normal', 'result_notes' => 'Dalam batas normal'],
            ['test' => $labTestModels['Glukosa Darah Puasa'], 'result_value' => '128', 'result_status' => 'abnormal', 'result_notes' => 'Sedikit di atas batas, masih terkontrol'],
        ], $dateOf(2)->copy()->setTime(10, 30));

        $labRequest($appointments['QUMU-20260815-001'], $mr6, 'completed', [
            ['test' => $labTestModels['Kolesterol Total'], 'result_value' => '245', 'result_status' => 'abnormal', 'result_notes' => 'Di atas batas normal'],
            ['test' => $labTestModels['Trigliserida'], 'result_value' => '172', 'result_status' => 'abnormal', 'result_notes' => 'Di atas batas normal'],
            ['test' => $labTestModels['Asam Urat'], 'result_value' => '8.1', 'result_status' => 'abnormal', 'result_notes' => 'Di atas batas normal'],
        ], $dateOf(4)->copy()->setTime(11, 0));

        $labRequest($appointments['QUMU-20260816-001'], $mr5, 'completed', [
            ['test' => $labTestModels['Golongan Darah'], 'result_value' => 'O', 'result_status' => 'normal'],
            ['test' => $labTestModels['Urine Lengkap'], 'result_value' => 'Dalam batas normal', 'result_status' => 'normal'],
        ], $dateOf(3)->copy()->setTime(11, 15));

        $labRequest($appointments['QUMU-20260812-001'], $mr9, 'completed', [
            ['test' => $labTestModels['Hemoglobin (Hb)'], 'result_value' => '13.8', 'result_status' => 'normal'],
        ], $dateOf(7)->copy()->setTime(10, 45));

        $labRequest($appointments['QUMU-20260819-002'], null, 'pending', [
            ['test' => $labTestModels['Leukosit']],
            ['test' => $labTestModels['Trombosit']],
        ], null, true);

        // Radiology requests
        $radiologyRequest = function (Appointment $appointment, ?MedicalRecord $record, string $status, array $items, ?Carbon $completedAt = null) use ($users) {
            $request = RadiologyRequest::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id' => $appointment->patient_id,
                    'doctor_id' => $appointment->doctor_id,
                    'medical_record_id' => $record?->id,
                    'created_by' => $users['doctor']->id,
                    'status' => $status,
                    'clinical_notes' => $items[0]['clinical_notes'] ?? null,
                    'completed_at' => $completedAt,
                ]
            );
            foreach ($items as $item) {
                $test = $item['test'];
                RadiologyRequestItem::firstOrCreate(
                    ['radiology_request_id' => $request->id, 'radiology_test_id' => $test->id],
                    [
                        'test_name' => $test->name,
                        'reference_range' => $test->reference_range,
                        'price' => $test->price,
                        'result_findings' => $item['findings'] ?? null,
                        'result_impression' => $item['impression'] ?? null,
                        'result_status' => $item['result_status'] ?? 'pending',
                    ]
                );
            }

            return $request;
        };

        $radiologyRequest($appointments['QKDG-20260818-001'], $mr4, 'completed', [
            [
                'test' => $radiologyTestModels['USG Obstetri'],
                'findings' => 'Janin aktif, DJJ 148 bpm, plasenta posterior, cairan ketuban cukup.',
                'impression' => 'Kehamilan normal, letak kepala.',
                'result_status' => 'normal',
            ],
        ], $dateOf(1)->copy()->setTime(14, 0));

        $radiologyRequest($appointments['QUMU-20260812-001'], $mr9, 'completed', [
            [
                'test' => $radiologyTestModels['Foto Thorax PA'],
                'findings' => 'Cor dan pulmo dalam batas normal, tidak tampak kardiomegali.',
                'impression' => 'Normal.',
                'result_status' => 'normal',
            ],
        ], $dateOf(7)->copy()->setTime(13, 0));

        $radiologyRequest($appointments['QUMU-20260819-001'], null, 'in_progress', [
            [
                'test' => $radiologyTestModels['Foto Thorax PA'],
                'clinical_notes' => 'Batuk 2 hari, suspek infeksi saluran napas.',
            ],
        ]);

        // ---------------------------------------------------------------------
        // Billing, payments & refunds
        // ---------------------------------------------------------------------
        $billing = function (Appointment $appointment, string $invoice, array $items, string $status, float $paidAmount, ?string $method, ?Carbon $paidAt) {
            $total = array_sum(array_column($items, 'subtotal'));
            $billing = Billing::updateOrCreate(
                ['invoice_number' => $invoice],
                [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'total_amount' => $total,
                    'paid_amount' => $paidAmount,
                    'discount' => 0,
                    'payment_method' => $method,
                    'status' => $status,
                    'paid_at' => $paidAt,
                    'notes' => $appointment->notes,
                ]
            );
            foreach ($items as $item) {
                BillingItem::firstOrCreate(
                    ['billing_id' => $billing->id, 'description' => $item['description']],
                    [
                        'type' => $item['type'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                    ]
                );
            }

            return $billing;
        };
        $payment = function (Billing $billing, string $method, float $amount, ?string $reference = null) use ($users) {
            BillingPayment::firstOrCreate(
                ['billing_id' => $billing->id, 'amount' => $amount, 'payment_method' => $method],
                ['reference' => $reference, 'processed_by' => $users['cashier']->id]
            );
        };

        // B1 appt3: paid cash
        $b1 = $billing($appointments['QPDL-20260817-001'], 'INV-20260817-0001', [
            ['description' => 'Pemeriksaan Dokter Spesialis (Penyakit Dalam)', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
            ['description' => 'Hemoglobin (Hb)', 'type' => 'lab', 'quantity' => 1, 'unit_price' => 25000, 'subtotal' => 25000],
            ['description' => 'Glukosa Darah Puasa', 'type' => 'lab', 'quantity' => 1, 'unit_price' => 35000, 'subtotal' => 35000],
        ], 'paid', 160000, 'cash', $dateOf(2)->copy()->setTime(13, 0));
        $payment($b1, 'cash', 160000);

        // B2 appt4: partial qris
        $b2 = $billing($appointments['QKDG-20260818-001'], 'INV-20260818-0001', [
            ['description' => 'Pemeriksaan Dokter Spesialis (Kandungan)', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 150000, 'subtotal' => 150000],
            ['description' => 'USG Obstetri', 'type' => 'tindakan', 'quantity' => 1, 'unit_price' => 200000, 'subtotal' => 200000],
        ], 'partial', 150000, 'qris', null);
        $payment($b2, 'qris', 150000, 'QRIS-REF-8821');

        // B3 appt5: overpaid -> refund
        $b3 = $billing($appointments['QUMU-20260816-001'], 'INV-20260816-0001', [
            ['description' => 'Pemeriksaan Dokter Umum', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
            ['description' => 'Rawat Luka Ringan', 'type' => 'tindakan', 'quantity' => 1, 'unit_price' => 40000, 'subtotal' => 40000],
            ['description' => 'Paracetamol 500mg', 'type' => 'obat', 'quantity' => 12, 'unit_price' => 1000, 'subtotal' => 12000],
            ['description' => 'Ibuprofen 400mg', 'type' => 'obat', 'quantity' => 10, 'unit_price' => 1500, 'subtotal' => 15000],
            ['description' => 'Cough Syrup', 'type' => 'obat', 'quantity' => 1, 'unit_price' => 7500, 'subtotal' => 7500],
        ], 'paid', 190000, 'cash', $dateOf(3)->copy()->setTime(13, 30));
        $payment($b3, 'cash', 190000);
        Refund::updateOrCreate(
            ['refund_number' => 'REF-20260816-0001'],
            [
                'billing_id' => $b3->id,
                'patient_id' => $appointments['QUMU-20260816-001']->patient_id,
                'amount' => 15500,
                'reason' => 'overpayment',
                'notes' => 'Kelebihan pembayaran tunai',
                'processed_by' => $users['cashier']->id,
                'refunded_at' => $dateOf(3)->copy()->setTime(14, 0),
            ]
        );

        // B4 appt6: unpaid
        $billing($appointments['QUMU-20260815-001'], 'INV-20260815-0001', [
            ['description' => 'Pemeriksaan Dokter Umum', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
            ['description' => 'Kolesterol Total', 'type' => 'lab', 'quantity' => 1, 'unit_price' => 40000, 'subtotal' => 40000],
            ['description' => 'Trigliserida', 'type' => 'lab', 'quantity' => 1, 'unit_price' => 40000, 'subtotal' => 40000],
            ['description' => 'Asam Urat', 'type' => 'lab', 'quantity' => 1, 'unit_price' => 35000, 'subtotal' => 35000],
        ], 'unpaid', 0, null, null);

        // B5 appt7: paid cash
        $b5 = $billing($appointments['QANK-20260813-001'], 'INV-20260813-0001', [
            ['description' => 'Pemeriksaan Dokter Spesialis (Anak)', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 125000, 'subtotal' => 125000],
        ], 'paid', 125000, 'cash', $dateOf(6)->copy()->setTime(12, 0));
        $payment($b5, 'cash', 125000);

        // B6 appt9: paid via BPJS
        $b6 = $billing($appointments['QUMU-20260812-001'], 'INV-20260812-0001', [
            ['description' => 'Pemeriksaan Dokter Umum', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
            ['description' => 'Foto Thorax PA', 'type' => 'tindakan', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
            ['description' => 'Amlodipine 5mg', 'type' => 'obat', 'quantity' => 30, 'unit_price' => 600, 'subtotal' => 18000],
        ], 'paid', 218000, 'bpjs', $dateOf(7)->copy()->setTime(13, 0));
        $payment($b6, 'bpjs', 218000, 'SEP-20260812-0001');

        // B7/B8: today's waiting appointments, unpaid
        $billing($appointments['QUMU-20260819-001'], 'INV-20260819-0001', [
            ['description' => 'Pemeriksaan Dokter Umum', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
        ], 'unpaid', 0, null, null);
        $billing($appointments['QUMU-20260819-002'], 'INV-20260819-0002', [
            ['description' => 'Pemeriksaan Dokter Umum', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 100000, 'subtotal' => 100000],
        ], 'unpaid', 0, null, null);

        // B9 appt8: cancelled service -> refund
        $b9 = $billing($appointments['QKDG-20260814-001'], 'INV-20260814-0001', [
            ['description' => 'Pemeriksaan Dokter Spesialis (Kandungan)', 'type' => 'konsultasi', 'quantity' => 1, 'unit_price' => 150000, 'subtotal' => 150000],
        ], 'cancelled', 150000, 'cash', $dateOf(5)->copy()->setTime(9, 0));
        $payment($b9, 'cash', 150000);
        Refund::updateOrCreate(
            ['refund_number' => 'REF-20260814-0001'],
            [
                'billing_id' => $b9->id,
                'patient_id' => $appointments['QKDG-20260814-001']->patient_id,
                'amount' => 150000,
                'reason' => 'cancelled_service',
                'notes' => 'Pasien membatalkan janji kontrol',
                'processed_by' => $users['cashier']->id,
                'refunded_at' => $dateOf(5)->copy()->setTime(9, 30),
            ]
        );

        // ---------------------------------------------------------------------
        // Inpatient admissions
        // ---------------------------------------------------------------------
        $admission = function (int $patientIdx, string $number, Carbon $admittedAt, ?Carbon $dischargedAt, string $roomKey, string $bedKey, int $doctorIdx, string $type, string $status, string $diagnosis, ?string $reason, ?string $notes) use ($patientModels, $doctorModels, $roomModels, $bedModels, $users) {
            return Admission::updateOrCreate(
                ['admission_number' => $number],
                [
                    'patient_id' => $patientModels[$patientIdx]->id,
                    'doctor_id' => $doctorModels[$doctorIdx]->id,
                    'room_id' => $roomModels[$roomKey]->id,
                    'bed_id' => $bedModels[$bedKey]->id,
                    'admission_type' => $type,
                    'status' => $status,
                    'admitted_at' => $admittedAt,
                    'discharged_at' => $dischargedAt,
                    'diagnosis' => $diagnosis,
                    'discharge_reason' => $reason,
                    'notes' => $notes,
                    'admitted_by' => $users['nurse']->id,
                    'discharged_by' => $dischargedAt ? $users['doctor']->id : null,
                ]
            );
        };

        $admission(4, 'INAP-20260819-001', $dateOf(0)->copy()->setTime(9, 00), null, 'ICU-01', 'ICU-01-01', 0, 'emergency', 'admitted', 'Apendisitis akut', null, 'Pasien dirujuk dari IGD, pre operasi apendektomi.');
        $admission(2, 'INAP-20260814-001', $dateOf(5)->copy()->setTime(10, 00), $dateOf(2)->copy()->setTime(11, 30), 'K2-01', 'K2-01-01', 0, 'elective', 'discharged', 'Kolesistitis kronik', 'Kondisi membaik, lanjut rawat jalan', 'Pasca kolesistektomi laparoskopi, kontrol poliklinik.');
        $admission(0, 'INAP-20260810-001', $dateOf(9)->copy()->setTime(8, 30), $dateOf(7)->copy()->setTime(16, 00), 'K3-01', 'K3-01-01', 0, 'elective', 'discharged', 'Observasi hipertensi', 'Tekanan darah terkontrol, pulang atas persetujuan', null);
        $admission(3, 'INAP-20260818-001', $dateOf(1)->copy()->setTime(15, 00), null, 'K1-01', 'K1-01-01', 2, 'elective', 'admitted', 'Observasi kehamilan', null, 'Observasi kehamilan trimester II, direncanakan USG.');

        // ---------------------------------------------------------------------
        // Emergency (IGD) visits
        // ---------------------------------------------------------------------
        $emergency = function (int $patientIdx, string $number, Carbon $arrivedAt, ?Carbon $dischargedAt, string $triage, string $complaint, string $status, array $vitals = [], ?string $referredTo = null, ?string $notes = null) use ($patientModels, $doctorModels, $users) {
            return EmergencyVisit::updateOrCreate(
                ['visit_number' => $number],
                array_merge([
                    'patient_id' => $patientModels[$patientIdx]->id,
                    'doctor_id' => $doctorModels[0]->id,
                    'created_by' => $users['nurse']->id,
                    'triage_level' => $triage,
                    'chief_complaint' => $complaint,
                    'status' => $status,
                    'referred_to' => $referredTo,
                    'discharge_notes' => $notes,
                    'arrived_at' => $arrivedAt,
                    'discharged_at' => $dischargedAt,
                    'discharged_by' => $dischargedAt ? $users['doctor']->id : null,
                ], $vitals)
            );
        };

        $emergency(4, 'IGD-20260819-0001', $dateOf(0)->copy()->setTime(8, 30), null, 'red', 'Nyeri perut kanan bawah hebat', 'admitted', [
            'triage_notes' => 'Nyeri akut, mual, demam 38.5C. Dirujuk ke rawat inap untuk apendektomi.',
            'temperature' => 38.5, 'blood_pressure_systolic' => 130, 'blood_pressure_diastolic' => 80,
            'heart_rate' => 110, 'respiratory_rate' => 24, 'oxygen_saturation' => 96, 'gcs' => 15,
        ]);
        $emergency(0, 'IGD-20260818-0001', $dateOf(1)->copy()->setTime(10, 00), $dateOf(1)->copy()->setTime(12, 00), 'green', 'Demam & batuk', 'discharged', [
            'temperature' => 37.8, 'blood_pressure_systolic' => 125, 'blood_pressure_diastolic' => 80,
            'heart_rate' => 92, 'respiratory_rate' => 20, 'oxygen_saturation' => 98, 'gcs' => 15,
            'triage_notes' => 'Keluhan ringan, pulang dengan obat simptomatik.',
        ]);
        $emergency(3, 'IGD-20260817-0001', $dateOf(2)->copy()->setTime(14, 00), null, 'yellow', 'Nyeri perut bagian bawah', 'observation', [
            'temperature' => 37.2, 'blood_pressure_systolic' => 115, 'blood_pressure_diastolic' => 75,
            'heart_rate' => 88, 'respiratory_rate' => 20, 'oxygen_saturation' => 99, 'gcs' => 15,
        ]);
        $emergency(2, 'IGD-20260814-0001', $dateOf(5)->copy()->setTime(9, 00), $dateOf(5)->copy()->setTime(11, 00), 'green', 'Nyeri ulu hati hebat', 'referred', [
            'temperature' => 36.9, 'blood_pressure_systolic' => 135, 'blood_pressure_diastolic' => 85,
            'heart_rate' => 90, 'respiratory_rate' => 20, 'oxygen_saturation' => 97, 'gcs' => 15,
        ], 'RSUD Kabupaten', 'Dirujuk untuk pemeriksaan endoskopi.');
        $emergency(6, 'IGD-20260809-0001', $dateOf(10)->copy()->setTime(06, 00), $dateOf(10)->copy()->setTime(07, 45), 'black', 'Ditemukan tidak sadarkan diri, henti napas', 'deceased', [
            'temperature' => null, 'blood_pressure_systolic' => null, 'blood_pressure_diastolic' => null,
            'heart_rate' => 0, 'respiratory_rate' => 0, 'oxygen_saturation' => 0, 'gcs' => 3,
            'triage_notes' => 'Pasien datang dalam keadaan meninggal. Dilakukan visum & keluarga diinformasikan.',
        ]);

        // ---------------------------------------------------------------------
        // Surgeries (OK)
        // ---------------------------------------------------------------------
        $surgery = function (int $patientIdx, string $number, Carbon $scheduledAt, ?Carbon $startedAt, ?Carbon $finishedAt, string $status, int $doctorIdx, string $procedure, string $type, ?string $icd9Code, string $room, ?string $pre, ?string $post) use ($patientModels, $doctorModels, $icd9Models, $users) {
            return Surgery::updateOrCreate(
                ['surgery_number' => $number],
                [
                    'patient_id' => $patientModels[$patientIdx]->id,
                    'doctor_id' => $doctorModels[$doctorIdx]->id,
                    'icd9_procedure_id' => $icd9Code ? $icd9Models[$icd9Code]->id : null,
                    'created_by' => $users['doctor']->id,
                    'procedure_name' => $procedure,
                    'surgery_type' => $type,
                    'operating_room' => $room,
                    'status' => $status,
                    'pre_notes' => $pre,
                    'post_notes' => $post,
                    'scheduled_at' => $scheduledAt,
                    'started_at' => $startedAt,
                    'finished_at' => $finishedAt,
                    'completed_by' => $startedAt ? $users['doctor']->id : null,
                ]
            );
        };

        $surgery(4, 'OK-20260819-001', $dateOf(0)->copy()->setTime(10, 00), $dateOf(0)->copy()->setTime(10, 15), $dateOf(0)->copy()->setTime(11, 30), 'completed', 0, 'Appendektomi (Appendectomy)', 'major', '47.09', 'OK-1', 'Apendisitis akut, indikasi operasi darurat.', 'Operasi berjalan lancar, jaringan diambil untuk patologi.');
        $surgery(2, 'OK-20260816-001', $dateOf(3)->copy()->setTime(9, 00), $dateOf(3)->copy()->setTime(9, 20), $dateOf(3)->copy()->setTime(11, 00), 'completed', 0, 'Kolesistektomi Laparoskopi (Laparoscopic Cholecystectomy)', 'major', '51.23', 'OK-2', 'Kolesistitis kronik kalkuli.', 'Pasca operasi stabil, direncanakan pulang.');
        $surgery(0, 'OK-20260821-001', $dateOf(-2)->copy()->setTime(13, 00), null, null, 'scheduled', 0, 'Eksisi Lipoma Regio Scapula', 'minor', null, 'OK-3', 'Lipoma 3 cm regio scapula kiri.', null);

        // ---------------------------------------------------------------------
        // Purchasing (PO)
        // ---------------------------------------------------------------------
        $purchaseOrder = function (int $supplierIdx, string $number, Carbon $orderDate, ?Carbon $receivedAt, string $status, array $items, ?string $notes = null) use ($supplierModels, $medicineModels, $users) {
            $total = array_sum(array_column($items, 'line_total'));
            $po = PurchaseOrder::updateOrCreate(
                ['po_number' => $number],
                [
                    'supplier_id' => $supplierModels[$supplierIdx]->id,
                    'created_by' => $users['pharmacist']->id,
                    'status' => $status,
                    'order_date' => $orderDate->toDateString(),
                    'expected_date' => $receivedAt ? null : $orderDate->copy()->addDays(3)->toDateString(),
                    'total_amount' => $total,
                    'notes' => $notes,
                    'received_at' => $receivedAt,
                ]
            );
            foreach ($items as $item) {
                PurchaseOrderItem::firstOrCreate(
                    ['purchase_order_id' => $po->id, 'medicine_id' => $item['medicine_id']],
                    [
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'line_total' => $item['line_total'],
                    ]
                );
            }

            return $po;
        };

        $purchaseOrder(0, 'PO-20260814-0001', $dateOf(5), null, 'ordered', [
            ['medicine_id' => $medicineModels['Paracetamol 500mg']->id, 'quantity' => 100, 'unit_price' => 500, 'line_total' => 50000],
            ['medicine_id' => $medicineModels['Amoxicillin 500mg']->id, 'quantity' => 50, 'unit_price' => 1200, 'line_total' => 60000],
        ], 'Restok obat analgesik & antibiotik.');

        $po2 = $purchaseOrder(1, 'PO-20260816-0001', $dateOf(3), $dateOf(3)->copy()->setTime(15, 00), 'received', [
            ['medicine_id' => $medicineModels['Amlodipine 5mg']->id, 'quantity' => 200, 'unit_price' => 300, 'line_total' => 60000],
            ['medicine_id' => $medicineModels['Metformin 500mg']->id, 'quantity' => 100, 'unit_price' => 400, 'line_total' => 40000],
        ], 'Penerimaan barang sesuai PO.');
        // Record received stock + mutation
        foreach ([
            ['medicine_id' => $medicineModels['Amlodipine 5mg']->id, 'quantity' => 200, 'batch' => 'BATCH-RCVD-001'],
            ['medicine_id' => $medicineModels['Metformin 500mg']->id, 'quantity' => 100, 'batch' => 'BATCH-RCVD-002'],
        ] as $received) {
            MedicineStock::firstOrCreate(
                ['medicine_id' => $received['medicine_id'], 'batch_number' => $received['batch']],
                ['quantity' => $received['quantity'], 'expiry_date' => $dateOf(-364)->toDateString()]
            );
            StockMutation::firstOrCreate(
                [
                    'medicine_id' => $received['medicine_id'],
                    'type' => 'in',
                    'quantity' => $received['quantity'],
                    'reference' => $po2->po_number,
                ],
                ['notes' => 'Penerimaan pembelian']
            );
        }

        $purchaseOrder(2, 'PO-20260818-0001', $dateOf(1), null, 'draft', [
            ['medicine_id' => $medicineModels['Cough Syrup']->id, 'quantity' => 20, 'unit_price' => 5000, 'line_total' => 100000],
        ], 'Draft rencana pembelian obat batuk.');

        // ---------------------------------------------------------------------
        // Expenses
        // ---------------------------------------------------------------------
        $expensesData = [
            ['expense_number' => 'EXP-20260804-0001', 'category' => 'utilitas', 'description' => 'Listrik & air bulanan', 'amount' => 5500000, 'expense_date' => '2026-08-04', 'paid_to' => 'PLN & PDAM', 'payment_method' => 'bank'],
            ['expense_number' => 'EXP-20260809-0001', 'category' => 'pemeliharaan', 'description' => 'Servis AC ruangan', 'amount' => 850000, 'expense_date' => '2026-08-09', 'paid_to' => 'CV Airtech', 'payment_method' => 'cash'],
            ['expense_number' => 'EXP-20260813-0001', 'category' => 'medis', 'description' => 'Pembelian alat medis habis pakai', 'amount' => 3200000, 'expense_date' => '2026-08-13', 'paid_to' => 'PT Medikalindo', 'payment_method' => 'bank'],
            ['expense_number' => 'EXP-20260816-0001', 'category' => 'administrasi', 'description' => 'ATK & kertas cetak', 'amount' => 350000, 'expense_date' => '2026-08-16', 'paid_to' => 'Toko ATK Sentosa', 'payment_method' => 'cash'],
            ['expense_number' => 'EXP-20260817-0001', 'category' => 'operasional', 'description' => 'Konsumsi rapat & operasional', 'amount' => 700000, 'expense_date' => '2026-08-17', 'paid_to' => 'Katering Sehat', 'payment_method' => 'cash'],
            ['expense_number' => 'EXP-20260818-0001', 'category' => 'gaji', 'description' => 'Honor tenaga honorer', 'amount' => 12000000, 'expense_date' => '2026-08-18', 'paid_to' => 'Staf honorer', 'payment_method' => 'bank'],
        ];
        foreach ($expensesData as $exp) {
            Expense::updateOrCreate(['expense_number' => $exp['expense_number']], [
                'category' => $exp['category'],
                'description' => $exp['description'],
                'amount' => $exp['amount'],
                'expense_date' => $exp['expense_date'],
                'paid_to' => $exp['paid_to'],
                'payment_method' => $exp['payment_method'],
                'created_by' => $users['cashier']->id,
            ]);
        }

        // ---------------------------------------------------------------------
        // Immunizations
        // ---------------------------------------------------------------------
        $immunization = function (int $patientIdx, ?Appointment $appointment, string $vaccine, ?string $dose, string $administeredAt, ?string $nextDue, string $batch, string $site, string $worker, ?string $notes) use ($patientModels, $users) {
            Immunization::firstOrCreate(
                ['patient_id' => $patientModels[$patientIdx]->id, 'vaccine_name' => $vaccine, 'dose' => $dose, 'administered_at' => $administeredAt],
                [
                    'appointment_id' => $appointment?->id,
                    'next_due_date' => $nextDue,
                    'batch_number' => $batch,
                    'site' => $site,
                    'healthcare_worker' => $worker,
                    'notes' => $notes,
                    'created_by' => $users['nurse']->id,
                ]
            );
        };

        $immunization(5, $appointments['QANK-20260813-001'], 'DPT', 'Dosis 1', '2026-08-13', '2026-09-13', 'DPT-2026-01', 'Paha kiri', 'dr. Budi, Sp.A', 'Tidak ada reaksi setelah imunisasi.');
        $immunization(5, $appointments['QANK-20260813-001'], 'OPV', 'Dosis 2', '2026-08-13', '2026-09-13', 'OPV-2026-02', 'Oral', 'dr. Budi, Sp.A', null);
        $immunization(5, $appointments['QANK-20260813-001'], 'MR', 'Dosis 1', '2026-08-13', '2026-09-13', 'MR-2026-03', 'Lengan kiri', 'dr. Budi, Sp.A', null);
        $immunization(3, null, 'TT', 'TT-2', '2026-08-18', '2027-02-18', 'TT-2026-04', 'Lengan kanan', 'dr. Clara, Sp.OG', 'Imunisasi TT pada ibu hamil.');
        $immunization(0, $appointments['QUMU-20260812-001'], 'Influenza', null, '2026-08-12', null, 'FLU-2026-05', 'Lengan kiri', 'dr. Andi, Sp.PD', 'Vaksinasi tahunan.');
        $immunization(0, null, 'COVID-19', 'Booster 2', '2026-07-20', null, 'COVID-2026-06', 'Lengan kiri', 'dr. Andi, Sp.PD', null);

        // ---------------------------------------------------------------------
        // Stock opnames
        // ---------------------------------------------------------------------
        $stockOpname = function (string $number, string $date, string $status, string $createdByName, string $notes, array $items) use ($medicineModels, $users) {
            $opname = StockOpname::updateOrCreate(
                ['opname_number' => $number],
                [
                    'opname_date' => $date,
                    'status' => $status,
                    'created_by_name' => $createdByName,
                    'notes' => $notes,
                    'created_by' => $users['pharmacist']->id,
                ]
            );
            foreach ($items as $item) {
                StockOpnameItem::firstOrCreate(
                    ['stock_opname_id' => $opname->id, 'medicine_id' => $item['medicine_id']],
                    [
                        'system_quantity' => $item['system'],
                        'actual_quantity' => $item['actual'],
                        'difference' => $item['actual'] - $item['system'],
                        'notes' => $item['notes'] ?? null,
                    ]
                );
            }

            return $opname;
        };

        $stockOpname('OPN-20260815-0001', '2026-08-15', 'approved', 'Rudi Staff Apoteker', 'Stok opname bulanan', [
            ['medicine_id' => $medicineModels['Paracetamol 500mg']->id, 'system' => 100, 'actual' => 98, 'notes' => 'Hilang 2 strip saat penggunaan'],
            ['medicine_id' => $medicineModels['Amoxicillin 500mg']->id, 'system' => 50, 'actual' => 50],
            ['medicine_id' => $medicineModels['Ibuprofen 400mg']->id, 'system' => 100, 'actual' => 97, 'notes' => 'Selisih 3 tablet'],
        ]);
        $stockOpname('OPN-20260819-0001', '2026-08-19', 'draft', 'Rudi Staff Apoteker', 'Opname berkala', [
            ['medicine_id' => $medicineModels['Amlodipine 5mg']->id, 'system' => 300, 'actual' => 302, 'notes' => 'Selisih lebih, verifikasi batch'],
            ['medicine_id' => $medicineModels['Cough Syrup']->id, 'system' => 20, 'actual' => 19],
        ]);

        // ---------------------------------------------------------------------
        // Death certificates
        // ---------------------------------------------------------------------
        DeathCertificate::updateOrCreate(
            ['certificate_number' => 'SK-20260809-0001'],
            [
                'patient_id' => $patientModels[6]->id,
                'date_of_death' => $dateOf(10)->copy()->setTime(07, 45),
                'place_of_death' => 'Instalasi Gawat Darurat',
                'cause_of_death' => 'cardiac',
                'diagnosis' => 'Infark Miokard Akut (AMI), henti jantung',
                'deceased_relation' => 'Anak kandung',
                'reporter_name' => 'Budi Santoso',
                'doctor_id' => $doctorModels[0]->id,
                'doctor_name' => 'dr. Andi, Sp.PD',
                'notes' => 'Penyebab kematian dikonfirmasi dari rekam medis.',
                'created_by' => $users['doctor']->id,
            ]
        );

        // ---------------------------------------------------------------------
        // BPJS (SEP & klaim)
        // ---------------------------------------------------------------------
        $sep = function (int $patientIdx, string $number, ?Appointment $appointment, string $bpjsNumber, string $jenis, string $date, string $diagnosis, ?string $poli, string $faskes) use ($patientModels, $users) {
            return SepRecord::updateOrCreate(
                ['sep_number' => $number],
                [
                    'patient_id' => $patientModels[$patientIdx]->id,
                    'appointment_id' => $appointment?->id,
                    'bpjs_number' => $bpjsNumber,
                    'jenis_pelayanan' => $jenis,
                    'sep_date' => $date,
                    'diagnosis' => $diagnosis,
                    'poli' => $poli,
                    'faskes_perujuk' => $faskes,
                    'status' => 'aktif',
                    'created_by' => $users['registration']->id,
                ]
            );
        };

        $sep5 = $sep(5, 'SEP-20260819-0001', $appointments['QANK-20260819-001'], '0001234567890123', 'rawat_jalan', '2026-08-19', 'Imunisasi dasar', 'Anak', 'Puskesmas Cempaka Putih');
        $sep4 = $sep(4, 'SEP-20260819-0002', null, '0009876543210987', 'rawat_inap', '2026-08-19', 'Apendisitis akut', null, 'RS Rujukan');
        $sep2 = $sep(2, 'SEP-20260817-0001', $appointments['QPDL-20260817-001'], '0005555555555555', 'rawat_jalan', '2026-08-17', 'DM tipe 2', 'Penyakit Dalam', 'Puskesmas Gambir');
        $sep0 = $sep(0, 'SEP-20260812-0001', $appointments['QUMU-20260812-001'], '0001234567890001', 'rawat_jalan', '2026-08-12', 'Hipertensi', 'Umum', 'Puskesmas Kemayoran');
        $sep6 = $sep(6, 'SEP-20260809-0001', null, '0007777777777777', 'rawat_inap', '2026-08-09', 'Infark Miokard Akut (AMI)', null, 'RS Rujukan');

        $claim = function (SepRecord $sepRecord, int $patientIdx, string $number, string $date, string $jenis, float $total, ?float $approved, string $status, ?string $notes) use ($patientModels, $users) {
            BpjsClaim::updateOrCreate(
                ['claim_number' => $number],
                [
                    'sep_record_id' => $sepRecord->id,
                    'patient_id' => $patientModels[$patientIdx]->id,
                    'claim_date' => $date,
                    'total_claim' => $total,
                    'approved_amount' => $approved,
                    'status' => $status,
                    'jenis_klaim' => $jenis,
                    'notes' => $notes,
                    'created_by' => $users['cashier']->id,
                ]
            );
        };

        $claim($sep2, 2, 'KLM-20260818-0001', '2026-08-18', 'rawat_jalan', 160000, 160000, 'disetujui', 'Klaim disetujui penuh.');
        $claim($sep4, 4, 'KLM-20260819-0001', '2026-08-19', 'rawat_inap', 4500000, null, 'menunggu', 'Menunggu verifikasi verifikator BPJS.');
        $claim($sep6, 6, 'KLM-20260812-0001', '2026-08-12', 'rawat_inap', 2500000, 2100000, 'disetujui', 'Klaim disetujui sebagian.');

        // ---------------------------------------------------------------------
        // Online registrations (antrian online)
        // ---------------------------------------------------------------------
        $onlineRegistration = function (string $number, string $name, ?string $nik, ?string $phone, ?string $dob, string $gender, string $poli, ?string $complaint, string $date, string $queue, string $status, ?Carbon $checkedInAt) {
            return OnlineRegistration::updateOrCreate(
                ['registration_number' => $number],
                [
                    'patient_name' => $name,
                    'nik' => $nik,
                    'phone' => $phone,
                    'date_of_birth' => $dob,
                    'gender' => $gender,
                    'poli' => $poli,
                    'complaint' => $complaint,
                    'registration_date' => $date,
                    'queue_number' => $queue,
                    'status' => $status,
                    'checked_in_at' => $checkedInAt,
                ]
            );
        };

        $onlineRegistration('AQ-20260819-0001', 'Naura Salsabila', '3171010119000006', '081234567890', '2019-03-10', 'P', 'Anak', 'Imunisasi', '2026-08-19', 'C-001', 'checked_in', $dateOf(0)->copy()->setTime(8, 05));
        $onlineRegistration('AQ-20260818-0001', 'Rudi Santoso', '3171010101880011', '082111223344', '1985-06-12', 'L', 'Umum', 'Pusing & demam', '2026-08-18', 'A-001', 'completed', $dateOf(1)->copy()->setTime(8, 00));
        $onlineRegistration('AQ-20260819-0002', 'Maya Sari', '3171010101920022', '085798765432', '1992-04-22', 'P', 'Penyakit Dalam', 'Kontrol DM', '2026-08-19', 'B-001', 'registered', null);
        $onlineRegistration('AQ-20260819-0003', 'Andi Pratama', '3171010101900033', '081399887766', '1990-01-15', 'L', 'Umum', 'Sakit tenggorokan', '2026-08-19', 'A-001', 'registered', null);
        $onlineRegistration('AQ-20260818-0002', 'Bunga Citra', '3171010101950044', '081255667788', '1995-09-30', 'P', 'Kandungan', 'Periksa kehamilan', '2026-08-18', 'E-001', 'cancelled', null);
    }
}