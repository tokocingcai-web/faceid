<?php
// hahahah
use App\Http\Controllers\Admin\AttendanceController as AttendanceManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentAttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/absen', [FaceController::class, 'scan'])->name('attendance.scan');
Route::get('/absen/pulang', [FaceController::class, 'scan'])->name('attendance.checkout');
Route::get('/face-login', [FaceController::class, 'scan'])->name('face.login');
Route::post('/absen/recognize', [FaceController::class, 'recognize'])
    ->middleware('throttle:30,1')
    ->name('attendance.recognize');

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware('auth')
    ->name('dashboard');

Route::middleware(['auth', 'role:dev'])->prefix('dev')->name('dev.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dev'])->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'guru'])->name('dashboard');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');
    Route::get('/riwayat-absensi', [StudentAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/riwayat-absensi/export', [StudentAttendanceController::class, 'export'])->name('attendance.export');
});

Route::middleware(['auth', 'role:wali'])->prefix('wali')->name('wali.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'wali'])->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/izin-sakit', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/izin-sakit/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/izin-sakit', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::delete('/izin-sakit/{leaveRequest}', [LeaveRequestController::class, 'destroy'])->name('leave-requests.destroy');
});

Route::middleware(['auth', 'role:admin,dev,guru'])->group(function () {
    Route::prefix('absensi')->name('attendance-manage.')->group(function () {
        Route::get('/', [AttendanceManagementController::class, 'index'])->name('index');
        Route::get('/{student}/edit', [AttendanceManagementController::class, 'edit'])->name('edit');
        Route::put('/{student}', [AttendanceManagementController::class, 'update'])->name('update');
        Route::delete('/{student}/{date}', [AttendanceManagementController::class, 'destroy'])
            ->where('date', '\d{4}-\d{2}-\d{2}')
            ->name('destroy');
    });

    Route::post('/izin-sakit/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('/izin-sakit/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

    Route::prefix('laporan')->name('reports.')->group(function () {
        Route::get('/harian', [ReportController::class, 'daily'])->name('daily');
        Route::get('/harian/export', [ReportController::class, 'dailyExport'])->name('daily.export');
        Route::get('/bulanan', [ReportController::class, 'monthly'])->name('monthly');
        Route::get('/bulanan/export', [ReportController::class, 'monthlyExport'])->name('monthly.export');
    });
});

Route::redirect('/scan-absen', '/absen');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
