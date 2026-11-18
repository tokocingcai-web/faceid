<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ParentLinkController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,dev'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/import', [UserController::class, 'importForm'])->name('users.import');
    Route::post('users/import', [UserController::class, 'importStore'])->name('users.import.store');
    Route::get('users/import/template', [UserController::class, 'template'])->name('users.import.template');
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::resource('users', UserController::class)->except('show');

    Route::post('academic-years/{academicYear}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
    Route::resource('academic-years', AcademicYearController::class)->except('show');
    Route::resource('subjects', SubjectController::class)->except('show');
    Route::resource('classes', SchoolClassController::class)->except('show')->parameters(['classes' => 'class']);
    Route::resource('schedules', ScheduleController::class)->except('show');

    Route::get('parent-links', [ParentLinkController::class, 'index'])->name('parent-links.index');
    Route::post('parent-links', [ParentLinkController::class, 'store'])->name('parent-links.store');
    Route::delete('parent-links/{parent}/{student}', [ParentLinkController::class, 'destroy'])->name('parent-links.destroy');
});
