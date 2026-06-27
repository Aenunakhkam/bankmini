<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

Route::get('/fix-db', function () {
    try {
        DB::statement('ALTER TABLE students ALTER COLUMN classroom_id DROP NOT NULL');
        return "Database berhasil diperbaiki! Silakan coba import lagi.";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/debug-db', function () {
    $columns = DB::select("
        SELECT column_name, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'students'
    ");
    return response()->json($columns);
});

Route::get('/test-insert', function () {
    try {
        DB::table('students')->insert([
            'nisn' => 'test_nisn_' . time(),
            'nis' => null,
            'name' => 'TEST STUDENT',
            'classroom_id' => null,
            'status' => 'Aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return "INSERT BERHASIL!";
    } catch (\Exception $e) {
        return "ERROR INSERT: " . $e->getMessage();
    }
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('majors', \App\Http\Controllers\MajorController::class);
    Route::resource('classrooms', \App\Http\Controllers\ClassroomController::class);
    Route::get('students/template', [\App\Http\Controllers\StudentController::class, 'template'])->name('students.template');
    Route::post('students/import', [\App\Http\Controllers\StudentController::class, 'import'])->name('students.import');
    Route::post('students/bulk-update-class', [\App\Http\Controllers\StudentController::class, 'bulkUpdateClass'])->name('students.bulk-update-class');
    Route::resource('students', \App\Http\Controllers\StudentController::class);
    
    Route::resource('academic-years', \App\Http\Controllers\AcademicYearController::class);
    Route::post('academic-years/{academic_year}/set-active', [\App\Http\Controllers\AcademicYearController::class, 'setActive'])->name('academic-years.set-active');

    Route::resource('payment-categories', \App\Http\Controllers\PaymentCategoryController::class);
    // Catatan Keuangan (Bank Mini)
    Route::get('/catatan-keuangan/pemasukan', [\App\Http\Controllers\CatatanKeuanganController::class, 'pemasukan'])->name('catatan-keuangan.pemasukan');
    Route::get('/catatan-keuangan/pemasukan/export-pdf', [\App\Http\Controllers\CatatanKeuanganController::class, 'exportPemasukanPdf'])->name('catatan-keuangan.pemasukan.pdf');
    Route::get('/catatan-keuangan/pemasukan/export-excel', [\App\Http\Controllers\CatatanKeuanganController::class, 'exportPemasukanExcel'])->name('catatan-keuangan.pemasukan.excel');
    
    Route::get('/catatan-keuangan/pengeluaran', [\App\Http\Controllers\CatatanKeuanganController::class, 'pengeluaran'])->name('catatan-keuangan.pengeluaran');
    Route::get('/catatan-keuangan/pengeluaran/export-pdf', [\App\Http\Controllers\CatatanKeuanganController::class, 'exportPengeluaranPdf'])->name('catatan-keuangan.pengeluaran.pdf');
    Route::get('/catatan-keuangan/pengeluaran/export-excel', [\App\Http\Controllers\CatatanKeuanganController::class, 'exportPengeluaranExcel'])->name('catatan-keuangan.pengeluaran.excel');
    
    Route::get('/catatan-keuangan/laporan', [\App\Http\Controllers\CatatanKeuanganController::class, 'laporan'])->name('catatan-keuangan.laporan');
    Route::get('/catatan-keuangan/laporan/export-pdf', [\App\Http\Controllers\CatatanKeuanganController::class, 'exportLaporanPdf'])->name('catatan-keuangan.laporan.pdf');
    Route::get('/catatan-keuangan/laporan/export-excel', [\App\Http\Controllers\CatatanKeuanganController::class, 'exportLaporanExcel'])->name('catatan-keuangan.laporan.excel');
    
    // (Old Simaku expenses routes removed based on plan)
    // Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class);
    // Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    
    // Billings & Payments
    Route::get('/billings/monitoring', [\App\Http\Controllers\BillingMonitoringController::class, 'index'])->name('billings.monitoring');
    Route::get('/billings/monitoring/print', [\App\Http\Controllers\BillingMonitoringController::class, 'printMassal'])->name('billings.monitoring.print');
    Route::resource('billings', \App\Http\Controllers\BillingController::class);
    // Teller (Bank Mini Core)
    Route::get('/teller', [\App\Http\Controllers\TellerController::class, 'index'])->name('teller.index');
    Route::post('/teller/search', [\App\Http\Controllers\TellerController::class, 'search'])->name('teller.search');
    Route::post('/teller/transaction', [\App\Http\Controllers\TellerController::class, 'store'])->name('teller.store');
    Route::get('/teller/receipt/{transaction}', [\App\Http\Controllers\TellerController::class, 'receipt'])->name('teller.receipt');
    Route::post('/teller/transaction/{id}/void', [\App\Http\Controllers\TellerController::class, 'voidTransaction'])->name('teller.void');
    Route::put('/teller/transaction/{id}', [\App\Http\Controllers\TellerController::class, 'updateTransaction'])->name('teller.update');
    Route::delete('/teller/transaction/{id}', [\App\Http\Controllers\TellerController::class, 'destroyTransaction'])->name('teller.destroy');
    Route::delete('/teller/student/{id}/reset', [\App\Http\Controllers\TellerController::class, 'resetStudent'])->name('teller.reset');

    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');

    Route::get('/payments', [\App\Http\Controllers\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/process/{student}', [\App\Http\Controllers\PaymentController::class, 'process'])->name('payments.process');
    Route::post('/payments/process/{student}', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}/invoice', [\App\Http\Controllers\PaymentController::class, 'printInvoicePdf'])->name('payments.invoice.pdf');
    Route::delete('/payments/{payment}', [\App\Http\Controllers\PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::get('/updates', [\App\Http\Controllers\UpdateController::class, 'index'])->name('updates');
    Route::post('/updates/pull', [\App\Http\Controllers\UpdateController::class, 'pull'])->name('updates.pull');
    Route::get('backup', [\App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
    Route::post('backup/create', [\App\Http\Controllers\BackupController::class, 'create'])->name('backup.create');
    Route::get('backup/download/{file}', [\App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    Route::delete('backup/delete/{file}', [\App\Http\Controllers\BackupController::class, 'delete'])->name('backup.delete');
    Route::post('backup/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('backup.restore');

    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/apply-admin-fee', [\App\Http\Controllers\SettingController::class, 'applyAdminFee'])->name('settings.apply-admin-fee');

    Route::get('/reports/classrooms', [\App\Http\Controllers\ReportController::class, 'classrooms'])->name('reports.classrooms');
    Route::get('/reports/classrooms/{id}', [\App\Http\Controllers\ReportController::class, 'classroomDetail'])->name('reports.classroom.detail');
    Route::get('/reports/classrooms/{id}/pdf', [\App\Http\Controllers\ReportController::class, 'printClassroomPdf'])->name('reports.classroom.pdf');
    Route::get('/reports/students/{id}/pdf', [\App\Http\Controllers\ReportController::class, 'printStudentPdf'])->name('reports.student.pdf');
    Route::get('/reports/bku/pdf', [\App\Http\Controllers\ReportController::class, 'printBkuPdf'])->name('reports.bku.pdf');
});