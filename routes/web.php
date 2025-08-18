<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;

// ------------------- PUBLIC ROUTES -------------------
Route::get('/', function() {
    return view("welcome");
})->name("home");

Route::get("/register", [UserController::class, 'registerPage'])->name('user.register');
Route::post("/register", [UserController::class, 'register'])->name('user.store');

Route::get("/login", [UserController::class, 'loginPage'])->name('login');
Route::post("/login", [UserController::class, 'login'])->name('user.login');

// ------------------- USER ROUTES -------------------
Route::middleware(["auth", 'role:user'])->group(function(){

    // Dashboard
    Route::get("/dashboard", [UserDashboardController::class,"index"])->name("user.dashboard");

    // Attendance records (AJAX)
    Route::get('/attendance', [UserDashboardController::class, 'getAttendance'])->name('user.attendance');

    // Mark attendance (simulate scan)
    Route::post('/mark-attendance', [UserDashboardController::class, 'markAttendance'])->name('user.simulateAttendance');
});

// ------------------- ADMIN ROUTES -------------------
Route::middleware(["auth",'role:admin'])->group(function(){

    // Admin Dashboard
    Route::get("/admin-dashboard", [AdminDashboardController::class,"index"])->name("admin.dashboard");

    // USERS CRUD
    Route::post('/admin/users/store', [AdminDashboardController::class, 'storeUser'])->name('admin.users.store');
    Route::patch('/admin/users/update/{id}', [AdminDashboardController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/delete/{id}', [AdminDashboardController::class, 'deleteUser'])->name('admin.users.destroy');

    // ATTENDANCE DELETION
    Route::delete('/admin/attendance/delete', [AdminDashboardController::class, 'deleteAttendance'])->name('admin.attendance.delete');
    Route::post('/admin/attendance/delete-filtered', [AdminDashboardController::class, 'deleteFilteredAttendance'])->name('admin.attendance.deleteFiltered');

    // DEPARTMENTS CRUD
    Route::post('/admin/departments/store', [DepartmentController::class, 'store'])->name('admin.departments.store');
    Route::patch('/admin/departments/update/{id}', [DepartmentController::class, 'update'])->name('admin.departments.update');
    Route::delete('/admin/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('admin.departments.delete');
});

// ------------------- LOGOUT -------------------
Route::get('/logout', function () {
    session()->flush();
    return redirect('/login');
})->name('logout');
