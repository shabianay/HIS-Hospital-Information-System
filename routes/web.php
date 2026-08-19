<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BpjsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeathCertificateController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EmergencyController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Icd9ProcedureController;
use App\Http\Controllers\ImmunizationController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnlineRegistrationController;
use App\Http\Controllers\MedicineStockController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PoliController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\QueueDisplayController;
use App\Http\Controllers\RadiologyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\SurgeryController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VitalSignController;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', fn () => redirect()->route('login'));

// Public queue lookup (patient-facing)
Route::get('/cek-antrian', [QueueDisplayController::class, 'lookupForm'])->name('queue.lookup');
Route::post('/cek-antrian', [QueueDisplayController::class, 'lookup'])->name('queue.lookup.search');

// Public portal (Antrian Online)
Route::get('/portal', [OnlineRegistrationController::class, 'portal'])->name('portal.index');
Route::get('/portal/status', [OnlineRegistrationController::class, 'lookup'])->name('portal.status');
Route::post('/portal/book', [OnlineRegistrationController::class, 'book'])->name('portal.book');
Route::post('/portal/cancel', [OnlineRegistrationController::class, 'cancel'])->name('portal.cancel');

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
    Route::get('appointments/queue/csv', [AppointmentController::class, 'queueCsv'])->name('appointments.queue.csv');
    Route::get('appointments/my-patients', [AppointmentController::class, 'myPatients'])->name('appointments.my-patients');
    Route::get('appointments/doctors-by-poli', [AppointmentController::class, 'doctorsByPoli'])->name('appointments.doctors-by-poli');
    Route::get('appointments/schedules-by-lookup', [AppointmentController::class, 'schedulesByLookup'])->name('appointments.schedules-by-lookup');
    Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
    Route::get('appointments/index/csv', [AppointmentController::class, 'indexCsv'])->name('appointments.index.csv');
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
    Route::get('patients/index/csv', [PatientController::class, 'indexCsv'])->name('patients.index.csv');
    Route::get('patients/search/json', [PatientController::class, 'search'])->name('patients.search');
    Route::get('patients/{patient}/card', [PatientController::class, 'card'])->name('patients.card');

    // Medical Records
    Route::get('medical-records', [MedicalRecordController::class, 'index'])->name('medical-records.index');
    Route::get('medical-records/index/csv', [MedicalRecordController::class, 'indexCsv'])->name('medical-records.index.csv');
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

    // Vital Signs (Tanda Vital) - input by nurse/registration
    Route::get('appointments/{appointment}/vital-sign', [VitalSignController::class, 'create'])->name('vital-signs.create');
    Route::post('appointments/{appointment}/vital-sign', [VitalSignController::class, 'store'])->name('vital-signs.store');

    // Doctor routes - accessible to admin and registration staff
    Route::resource('doctors', DoctorController::class);
    Route::get('doctors/index/csv', [DoctorController::class, 'indexCsv'])->name('doctors.index.csv');

    // Poli routes
    Route::resource('polis', PoliController::class);
    Route::get('polis/index/csv', [PoliController::class, 'indexCsv'])->name('polis.index.csv');

    // Schedule routes
    Route::get('schedules/board', [ScheduleController::class, 'board'])->name('schedules.board');
    Route::resource('schedules', ScheduleController::class);
    Route::get('schedules/index/csv', [ScheduleController::class, 'indexCsv'])->name('schedules.index.csv');

    // Medicines & Pharmacy
    Route::resource('medicines', MedicineController::class);
    Route::get('medicines/index/csv', [MedicineController::class, 'indexCsv'])->name('medicines.index.csv');
    Route::get('medicines-stock', [MedicineController::class, 'stock'])->name('medicines.stock');
    Route::get('medicine-mutations', [MedicineController::class, 'mutations'])->name('medicines.mutations');
    Route::get('medicine-mutations/csv', [MedicineController::class, 'mutationsCsv'])->name('medicines.mutations.csv');
    Route::get('medicine-reorder', [MedicineController::class, 'reorder'])->name('medicines.reorder');
    Route::get('medicine-reorder/pdf', [MedicineController::class, 'reorderPdf'])->name('medicines.reorder.pdf');
    Route::get('medicine-expiring', [MedicineController::class, 'expiring'])->name('medicines.expiring');
    Route::get('medicine-stock-card', [MedicineController::class, 'stockCard'])->name('medicines.stock-card');
    Route::post('medicine-stocks', [MedicineStockController::class, 'store'])->name('medicine-stocks.store');
    Route::post('medicine-stocks/adjust', [MedicineStockController::class, 'adjust'])->name('medicine-stocks.adjust');
    Route::post('medicine-stocks/retur', [MedicineStockController::class, 'retur'])->name('medicine-stocks.retur');
    Route::post('prescriptions/{prescription}/dispense', [MedicineStockController::class, 'dispense'])->name('prescriptions.dispense');
    Route::get('pharmacy/pending', [MedicineStockController::class, 'pending'])->name('prescriptions.pending');

    // Inpatient (Rawat Inap)
    Route::resource('rooms', RoomController::class);
    Route::get('rooms/index/csv', [RoomController::class, 'indexCsv'])->name('rooms.index.csv');
    Route::resource('beds', BedController::class)->except(['show']);
    Route::get('beds/index/csv', [BedController::class, 'indexCsv'])->name('beds.index.csv');
    Route::get('inpatient-admissions', [AdmissionController::class, 'index'])->name('admissions.index');
    Route::get('inpatient-admissions/csv', [AdmissionController::class, 'indexCsv'])->name('admissions.index.csv');
    Route::get('inpatient-admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('inpatient-admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    Route::get('inpatient-admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
    Route::patch('inpatient-admissions/{admission}/discharge', [AdmissionController::class, 'discharge'])->name('admissions.discharge');

    // Billing
    Route::get('billings', [BillingController::class, 'index'])->name('billings.index');
    Route::get('billings/create/{appointment}', [BillingController::class, 'create'])->name('billings.create');
    Route::post('billings', [BillingController::class, 'store'])->name('billings.store');
    Route::get('billings/{billing}', [BillingController::class, 'show'])->name('billings.show');
    Route::patch('billings/{billing}', [BillingController::class, 'update'])->name('billings.update');
    Route::patch('billings/{billing}/payment', [BillingController::class, 'processPayment'])->name('billings.payment');
    Route::get('billings/{billing}/receipt', [BillingController::class, 'receipt'])->name('billings.receipt');
    Route::get('billings/{billing}/receipt/pdf', [BillingController::class, 'receiptPdf'])->name('billings.receipt.pdf');
    Route::get('billing/daily-report', [BillingController::class, 'dailyReport'])->name('billings.daily-report');
    Route::get('billing/daily-report/pdf', [BillingController::class, 'dailyReportPdf'])->name('billings.daily-report.pdf');
    Route::get('billing/daily-report/csv', [BillingController::class, 'dailyReportCsv'])->name('billings.daily-report.csv');
    Route::get('billing/cash-reconciliation', [BillingController::class, 'cashReconciliation'])->name('billings.reconciliation');
    Route::post('billing/cash-reconciliation/{shift}', [BillingController::class, 'cashReconciliationStore'])->name('billings.reconciliation.store');

    // Tariffs
    Route::resource('tariffs', TariffController::class);
    Route::get('tariffs/index/csv', [TariffController::class, 'indexCsv'])->name('tariffs.index.csv');

    // Laboratory
    Route::get('lab/tests', [LabController::class, 'tests'])->name('lab.tests');
    Route::get('lab/tests/csv', [LabController::class, 'testsCsv'])->name('lab.tests.csv');
    Route::post('lab/tests', [LabController::class, 'testsStore'])->name('lab.tests.store');
    Route::put('lab/tests/{labTest}', [LabController::class, 'testsUpdate'])->name('lab.tests.update');
    Route::delete('lab/tests/{labTest}', [LabController::class, 'testsDestroy'])->name('lab.tests.destroy');

    Route::get('lab/requests', [LabController::class, 'index'])->name('lab.requests');
    Route::get('lab/requests/csv', [LabController::class, 'exportCsv'])->name('lab.requests.csv');
    Route::get('lab/create', [LabController::class, 'create'])->name('lab.create');
    Route::post('lab/requests', [LabController::class, 'store'])->name('lab.requests.store');
    Route::get('lab/requests/{labRequest}', [LabController::class, 'show'])->name('lab.requests.show');
    Route::get('lab/requests/{labRequest}/pdf', [LabController::class, 'exportPdf'])->name('lab.requests.pdf');
    Route::post('lab/requests/{labRequest}/results', [LabController::class, 'processStore'])->name('lab.requests.process');
    Route::delete('lab/requests/{labRequest}', [LabController::class, 'destroy'])->name('lab.requests.destroy');

    // Radiology
    Route::get('radiology/tests', [RadiologyController::class, 'tests'])->name('radiology.tests');
    Route::get('radiology/tests/csv', [RadiologyController::class, 'testsCsv'])->name('radiology.tests.csv');
    Route::post('radiology/tests', [RadiologyController::class, 'testsStore'])->name('radiology.tests.store');
    Route::put('radiology/tests/{radiologyTest}', [RadiologyController::class, 'testsUpdate'])->name('radiology.tests.update');
    Route::delete('radiology/tests/{radiologyTest}', [RadiologyController::class, 'testsDestroy'])->name('radiology.tests.destroy');

    Route::get('radiology/requests', [RadiologyController::class, 'index'])->name('radiology.requests');
    Route::get('radiology/requests/csv', [RadiologyController::class, 'exportCsv'])->name('radiology.requests.csv');
    Route::get('radiology/create', [RadiologyController::class, 'create'])->name('radiology.create');
    Route::post('radiology/requests', [RadiologyController::class, 'store'])->name('radiology.requests.store');
    Route::get('radiology/requests/{radiologyRequest}', [RadiologyController::class, 'show'])->name('radiology.requests.show');
    Route::get('radiology/requests/{radiologyRequest}/pdf', [RadiologyController::class, 'exportPdf'])->name('radiology.requests.pdf');
    Route::post('radiology/requests/{radiologyRequest}/results', [RadiologyController::class, 'processStore'])->name('radiology.requests.process');
    Route::delete('radiology/requests/{radiologyRequest}', [RadiologyController::class, 'destroy'])->name('radiology.requests.destroy');

    // Emergency (IGD)
    Route::get('emergency', [EmergencyController::class, 'index'])->name('emergency.index');
    Route::get('emergency/csv', [EmergencyController::class, 'exportCsv'])->name('emergency.csv');
    Route::get('emergency/create', [EmergencyController::class, 'create'])->name('emergency.create');
    Route::post('emergency', [EmergencyController::class, 'store'])->name('emergency.store');
    Route::get('emergency/{emergencyVisit}', [EmergencyController::class, 'show'])->name('emergency.show');
    Route::put('emergency/{emergencyVisit}', [EmergencyController::class, 'update'])->name('emergency.update');

    // ICD-9-CM Master
    Route::get('icd9', [Icd9ProcedureController::class, 'index'])->name('icd9.index');
    Route::get('icd9/index/csv', [Icd9ProcedureController::class, 'indexCsv'])->name('icd9.csv');
    Route::post('icd9', [Icd9ProcedureController::class, 'store'])->name('icd9.store');
    Route::put('icd9/{icd9Procedure}', [Icd9ProcedureController::class, 'update'])->name('icd9.update');
    Route::delete('icd9/{icd9Procedure}', [Icd9ProcedureController::class, 'destroy'])->name('icd9.destroy');

    // Surgery (OK)
    Route::get('surgeries', [SurgeryController::class, 'index'])->name('surgeries.index');
    Route::get('surgeries/csv', [SurgeryController::class, 'exportCsv'])->name('surgeries.csv');
    Route::get('surgeries/create', [SurgeryController::class, 'create'])->name('surgeries.create');
    Route::post('surgeries', [SurgeryController::class, 'store'])->name('surgeries.store');
    Route::get('surgeries/{surgery}', [SurgeryController::class, 'show'])->name('surgeries.show');
    Route::patch('surgeries/{surgery}/status', [SurgeryController::class, 'updateStatus'])->name('surgeries.status');
    Route::delete('surgeries/{surgery}', [SurgeryController::class, 'destroy'])->name('surgeries.destroy');

    // Purchasing (Supplier & PO)
    Route::get('purchasing/suppliers', [PurchasingController::class, 'suppliers'])->name('purchasing.suppliers');
    Route::get('purchasing/suppliers/csv', [PurchasingController::class, 'suppliersCsv'])->name('purchasing.suppliers.csv');
    Route::post('purchasing/suppliers', [PurchasingController::class, 'supplierStore'])->name('purchasing.suppliers.store');
    Route::put('purchasing/suppliers/{supplier}', [PurchasingController::class, 'supplierUpdate'])->name('purchasing.suppliers.update');
    Route::delete('purchasing/suppliers/{supplier}', [PurchasingController::class, 'supplierDestroy'])->name('purchasing.suppliers.destroy');

    Route::get('purchasing/orders', [PurchasingController::class, 'orders'])->name('purchasing.orders');
    Route::get('purchasing/orders/csv', [PurchasingController::class, 'ordersCsv'])->name('purchasing.orders.csv');
    Route::get('purchasing/orders/create', [PurchasingController::class, 'ordersCreate'])->name('purchasing.orders.create');
    Route::post('purchasing/orders', [PurchasingController::class, 'ordersStore'])->name('purchasing.orders.store');
    Route::get('purchasing/orders/{purchaseOrder}', [PurchasingController::class, 'ordersShow'])->name('purchasing.orders.show');
    Route::patch('purchasing/orders/{purchaseOrder}/status', [PurchasingController::class, 'ordersStatus'])->name('purchasing.orders.status');
    Route::delete('purchasing/orders/{purchaseOrder}', [PurchasingController::class, 'ordersDestroy'])->name('purchasing.orders.destroy');

    // Finance (Expense)
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/csv', [ExpenseController::class, 'exportCsv'])->name('expenses.csv');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Refund
    Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
    Route::get('refunds/csv', [RefundController::class, 'exportCsv'])->name('refunds.csv');
    Route::get('refunds/create', [RefundController::class, 'create'])->name('refunds.create');
    Route::post('refunds', [RefundController::class, 'store'])->name('refunds.store');

    // Immunization
    Route::get('immunizations', [ImmunizationController::class, 'index'])->name('immunizations.index');
    Route::get('immunizations/csv', [ImmunizationController::class, 'exportCsv'])->name('immunizations.csv');
    Route::get('immunizations/create', [ImmunizationController::class, 'create'])->name('immunizations.create');
    Route::post('immunizations', [ImmunizationController::class, 'store'])->name('immunizations.store');
    Route::delete('immunizations/{immunization}', [ImmunizationController::class, 'destroy'])->name('immunizations.destroy');

    // Stock Opname
    Route::get('stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');
    Route::get('stock-opname/create', [StockOpnameController::class, 'create'])->name('stock-opname.create');
    Route::post('stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');
    Route::get('stock-opname/{stockOpname}', [StockOpnameController::class, 'show'])->name('stock-opname.show');
    Route::post('stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
    Route::delete('stock-opname/{stockOpname}', [StockOpnameController::class, 'destroy'])->name('stock-opname.destroy');

    // Death Certificates
    Route::get('death-certificates', [DeathCertificateController::class, 'index'])->name('death-certificates.index');
    Route::get('death-certificates/csv', [DeathCertificateController::class, 'exportCsv'])->name('death-certificates.csv');
    Route::get('death-certificates/create', [DeathCertificateController::class, 'create'])->name('death-certificates.create');
    Route::post('death-certificates', [DeathCertificateController::class, 'store'])->name('death-certificates.store');
    Route::get('death-certificates/{deathCertificate}/pdf', [DeathCertificateController::class, 'show'])->name('death-certificates.pdf');
    Route::delete('death-certificates/{deathCertificate}', [DeathCertificateController::class, 'destroy'])->name('death-certificates.destroy');

    // BPJS
    Route::get('bpjs', [BpjsController::class, 'index'])->name('bpjs.index');
    Route::get('bpjs/csv', [BpjsController::class, 'exportCsv'])->name('bpjs.csv');
    Route::post('bpjs/sep', [BpjsController::class, 'storeSep'])->name('bpjs.sep.store');
    Route::post('bpjs/sep/{sepRecord}/cancel', [BpjsController::class, 'cancelSep'])->name('bpjs.sep.cancel');
    Route::post('bpjs/claims', [BpjsController::class, 'storeClaim'])->name('bpjs.claim.store');
    Route::post('bpjs/claims/{bpjsClaim}/status', [BpjsController::class, 'updateClaimStatus'])->name('bpjs.claim.status');

    // Online Registrations (admin)
    Route::get('online-registrations', [OnlineRegistrationController::class, 'index'])->name('online-registrations.index');
    Route::get('online-registrations/portal', [OnlineRegistrationController::class, 'portal'])->name('online-registrations.portal');
    Route::get('online-registrations/csv', [OnlineRegistrationController::class, 'exportCsv'])->name('online-registrations.csv');
    Route::post('online-registrations/{onlineRegistration}/checkin', [OnlineRegistrationController::class, 'checkIn'])->name('online-registrations.checkin');
    Route::post('online-registrations/{onlineRegistration}/complete', [OnlineRegistrationController::class, 'complete'])->name('online-registrations.complete');

    // Admin: User Management
    Route::resource('users', UserController::class);
    Route::get('users/index/csv', [UserController::class, 'indexCsv'])->name('users.index.csv');
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
