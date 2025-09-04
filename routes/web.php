<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AdminAttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

// ------------------- PUBLIC ROUTES -------------------
Route::get('/', function() {
    return view("welcome");
})->name("home");

Route::get("/register", [UserController::class, 'registerPage'])->name('user.register');
Route::post("/register", [UserController::class, 'register'])->name('user.store');

Route::get("/login", [UserController::class, 'loginPage'])->name('login');
Route::post("/login", [UserController::class, 'login'])->name('user.login');

// ------------------- FORCE PASSWORD CHANGE -------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/force-change-password', [UserDashboardController::class, 'showForceChangePassword'])
        ->name('force.change.password');
    Route::post('/force-change-password', [UserDashboardController::class, 'updateForceChangePassword'])
        ->name('force.change.password.update');
});

// ------------------- USER ROUTES -------------------
Route::middleware(["auth", 'role:User', 'force.password'])->group(function(){

    // Dashboard
    Route::get("/dashboard", [UserDashboardController::class,"index"])->name("user.dashboard");

    // Attendance records
    Route::get('/attendance', [UserDashboardController::class, 'getAttendance'])->name('user.attendance');

    // Mark attendance (simulate scan)
    Route::post('/mark-attendance', [UserDashboardController::class, 'markAttendance'])->name('user.simulateAttendance');
});

// ------------------- ADMIN + SUPER ADMIN ROUTES -------------------
Route::middleware(['auth', 'role:Admin|Super Admin'])->group(function () {

    // Admin Dashboard
    Route::get("/admin-dashboard", [AdminDashboardController::class,"index"])->name("admin.dashboard");

    // ------------------- USERS -------------------
    Route::get('/admin/users', [AdminDashboardController::class, 'usersIndex'])->name('admin.users.index');
    Route::post('/admin/users/store', [AdminDashboardController::class, 'storeUser'])->name('admin.users.store');
    Route::patch('/admin/users/update/{id}', [AdminDashboardController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/delete/{id}', [AdminDashboardController::class, 'deleteUser'])->name('admin.users.destroy');

    // ------------------- DEPARTMENTS -------------------
    Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments.index');
    Route::post('/admin/departments/store', [DepartmentController::class, 'store'])->name('admin.departments.store');
    Route::patch('/admin/departments/update/{id}', [DepartmentController::class, 'update'])->name('admin.departments.update');
    Route::delete('/admin/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('admin.departments.delete');

    // ------------------- ATTENDANCE REPORTS -------------------
    Route::get('/admin/attendance-reports', [AdminDashboardController::class, 'attendanceReports'])->name('admin.attendance.reports');
    Route::delete('/admin/attendance/delete', [AdminDashboardController::class, 'deleteFilteredAttendance'])->name('admin.attendance.delete');
    Route::post('/admin/attendance/delete-filtered', [AdminDashboardController::class, 'deleteFilteredAttendance'])->name('admin.attendance.deleteFiltered');

    // ------------------- MANUAL ATTENDANCE -------------------
    Route::get('/admin/attendance/manual', [AdminAttendanceController::class, 'showForm'])->name('attendance.manual');
    Route::post('/admin/attendance/manual', [AdminAttendanceController::class, 'store'])->name('attendance.manual.store');
});

// ------------------- SUPER ADMIN ONLY ROUTES -------------------
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    // ------------------- AUDITS -------------------
    Route::get('/admin/audits', [AdminDashboardController::class, 'audits'])->name('admin.audits');
    Route::delete('/admin/audits/delete/{id}', [AdminDashboardController::class, 'deleteAudit'])->name('admin.audits.delete');
    Route::post('/admin/audits/delete-filtered', [AdminDashboardController::class, 'deleteFilteredAudits'])->name('admin.audits.deleteFiltered');
});

// ------------------- LOGOUT -------------------
Route::get('/logout', function () {
    session()->flush();
    return redirect('/login');
})->name('logout');


Route::get('/fingerprint/attendance', [AttendanceController::class, 'mark']);
