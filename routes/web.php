<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MedicineStockController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QueueDisplayController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', fn () => redirect()->route('login'));

// Auth routes (from Breeze)
require __DIR__.'/auth.php';

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Profile (keep Breeze profile routes)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// All authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('reports/csv', [ReportController::class, 'exportCsv'])->name('reports.csv');

    // Registration & Queue
    Route::get('appointments/queue', [AppointmentController::class, 'queue'])->name('appointments.queue');
    Route::get('appointments/my-patients', [AppointmentController::class, 'myPatients'])->name('appointments.my-patients');
    Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status.update');
    Route::get('appointments/{appointment}/ticket', [AppointmentController::class, 'ticket'])->name('appointments.ticket');
    Route::get('queue-display', [QueueDisplayController::class, 'index'])->name('queue.display');
    Route::get('queue-display/json', [QueueDisplayController::class, 'getCurrentQueues'])->name('queue.display.json');
    Route::get('queue-display/lab', [QueueDisplayController::class, 'lab'])->name('queue.display.lab');
    Route::get('queue-display/lab/json', [QueueDisplayController::class, 'getLabQueues'])->name('queue.display.lab.json');
    Route::get('queue-display/pharmacy', [QueueDisplayController::class, 'pharmacy'])->name('queue.display.pharmacy');
    Route::get('queue-display/pharmacy/json', [QueueDisplayController::class, 'getPharmacyQueues'])->name('queue.display.pharmacy.json');

    // Patients
    Route::resource('patients', PatientController::class);
    Route::get('patients/search/json', [PatientController::class, 'search'])->name('patients.search');
    Route::get('patients/{patient}/card', [PatientController::class, 'card'])->name('patients.card');

    // Medical Records
    Route::get('medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');
    Route::get('patients/{patient}/medical-history', [MedicalRecordController::class, 'history'])->name('patients.medical-history');
    Route::get('patients/{patient}/medical-history/pdf', [MedicalRecordController::class, 'historyPdf'])->name('patients.medical-history.pdf');
    Route::get('appointments/{appointment}/medical-record/create', [MedicalRecordController::class, 'create'])->name('medical-records.create');
    Route::post('appointments/{appointment}/medical-record', [MedicalRecordController::class, 'store'])->name('medical-records.store');
    Route::get('medical-records/{medicalRecord}', [MedicalRecordController::class, 'show'])->name('medical-records.show');
    Route::get('medical-records/{medicalRecord}/pdf', [MedicalRecordController::class, 'exportPdf'])->name('medical-records.pdf');
    Route::get('medical-records/{medicalRecord}/sick-note', [MedicalRecordController::class, 'sickNotePdf'])->name('medical-records.sick-note');
    Route::get('medical-records/{medicalRecord}/prescription', [MedicalRecordController::class, 'prescriptionPdf'])->name('medical-records.prescription');
    Route::get('medical-records/{medicalRecord}/referral', [MedicalRecordController::class, 'referralPdf'])->name('medical-records.referral');
    Route::get('medical-records/{medicalRecord}/edit', [MedicalRecordController::class, 'edit'])->name('medical-records.edit');
    Route::put('medical-records/{medicalRecord}', [MedicalRecordController::class, 'update'])->name('medical-records.update');

    // Doctor routes - accessible to admin and registration staff
    Route::resource('doctors', DoctorController::class);

    // Poli routes
    Route::resource('polis', PoliController::class);

    // Schedule routes
    Route::get('schedules/board', [ScheduleController::class, 'board'])->name('schedules.board');
    Route::resource('schedules', ScheduleController::class);

    // Medicines & Pharmacy
    Route::resource('medicines', MedicineController::class);
    Route::get('medicines-stock', [MedicineController::class, 'stock'])->name('medicines.stock');
    Route::get('medicine-mutations', [MedicineController::class, 'mutations'])->name('medicines.mutations');
    Route::get('medicine-reorder', [MedicineController::class, 'reorder'])->name('medicines.reorder');
    Route::post('medicine-stocks', [MedicineStockController::class, 'store'])->name('medicine-stocks.store');
    Route::post('medicine-stocks/adjust', [MedicineStockController::class, 'adjust'])->name('medicine-stocks.adjust');
    Route::post('prescriptions/{prescription}/dispense', [MedicineStockController::class, 'dispense'])->name('prescriptions.dispense');
    Route::get('pharmacy/pending', [MedicineStockController::class, 'pending'])->name('prescriptions.pending');

    // Billing
    Route::get('billings', [BillingController::class, 'index'])->name('billings.index');
    Route::get('billings/create/{appointment}', [BillingController::class, 'create'])->name('billings.create');
    Route::post('billings', [BillingController::class, 'store'])->name('billings.store');
    Route::get('billings/{billing}', [BillingController::class, 'show'])->name('billings.show');
    Route::patch('billings/{billing}', [BillingController::class, 'update'])->name('billings.update');
    Route::patch('billings/{billing}/payment', [BillingController::class, 'processPayment'])->name('billings.payment');
    Route::get('billings/{billing}/receipt', [BillingController::class, 'receipt'])->name('billings.receipt');
    Route::get('billing/daily-report', [BillingController::class, 'dailyReport'])->name('billings.daily-report');
    Route::get('billing/daily-report/pdf', [BillingController::class, 'dailyReportPdf'])->name('billings.daily-report.pdf');
    Route::get('billing/daily-report/csv', [BillingController::class, 'dailyReportCsv'])->name('billings.daily-report.csv');

    // Tariffs
    Route::resource('tariffs', TariffController::class);

    // Laboratory
    Route::get('lab/tests', [LabController::class, 'tests'])->name('lab.tests');
    Route::post('lab/tests', [LabController::class, 'testsStore'])->name('lab.tests.store');
    Route::put('lab/tests/{labTest}', [LabController::class, 'testsUpdate'])->name('lab.tests.update');
    Route::delete('lab/tests/{labTest}', [LabController::class, 'testsDestroy'])->name('lab.tests.destroy');

    Route::get('lab/requests', [LabController::class, 'index'])->name('lab.requests');
    Route::get('lab/create', [LabController::class, 'create'])->name('lab.create');
    Route::post('lab/requests', [LabController::class, 'store'])->name('lab.requests.store');
    Route::get('lab/requests/{labRequest}', [LabController::class, 'show'])->name('lab.requests.show');
    Route::get('lab/requests/{labRequest}/pdf', [LabController::class, 'exportPdf'])->name('lab.requests.pdf');
    Route::post('lab/requests/{labRequest}/results', [LabController::class, 'processStore'])->name('lab.requests.process');
    Route::delete('lab/requests/{labRequest}', [LabController::class, 'destroy'])->name('lab.requests.destroy');

    // Admin: User Management
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/export/csv', [AuditController::class, 'exportCsv'])->name('audit.export.csv');
    Route::get('audit/export/pdf', [AuditController::class, 'exportPdf'])->name('audit.export.pdf');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::delete('notifications', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
});
