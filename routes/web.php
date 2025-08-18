<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\DepartmentController;
use Illuminate\Support\Facades\Route;

// ---------------------- PUBLIC ROUTES ----------------------
Route::get('/', function(){
    return view("welcome");
})->name("home");

Route::get("/register", [UserController::class, 'registerPage'])->name('user.register');
Route::post("/register", [UserController::class, 'register'])->name('user.store');

Route::get("/login", [UserController::class, 'loginPage'])->name('login');
Route::post("/login", [UserController::class, 'login'])->name('user.login');

// ---------------------- LOGOUT ----------------------
Route::get('/logout', function () {
    session()->flush(); // Clear all session data
    return redirect('/login');
})->name('logout');

// ---------------------- FORCE PASSWORD CHANGE ----------------------
// Routes **only for users** to force change password
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/force-change-password', [UserDashboardController::class, 'showForceChangePassword'])
        ->name('force.change.password');

    Route::post('/force-change-password', [UserDashboardController::class, 'updateForceChangePassword'])
        ->name('force.change.password.update');
});

// ---------------------- USER ROUTES ----------------------
// Add 'force.password' middleware to ensure first login users must change password
Route::middleware(["auth", 'role:user', 'force.password'])->group(function(){
    Route::get("/dashboard", [UserDashboardController::class,"index" ])->name("user.dashboard");
    Route::get('/attendance', [UserDashboardController::class, 'getAttendance'])->name('user.attendance');
    Route::post('/mark-attendance', [UserDashboardController::class, 'markAttendance'])->name('user.markAttendance');
});

// ---------------------- ADMIN ROUTES ----------------------
// Admins should **not** be forced to change password
Route::middleware(["auth",'role:admin'])->group(function(){
    Route::get("/admin-dashboard", [AdminDashboardController::class,"index"])->name("admin.dashboard");

    // Departments
    Route::post('/admin/departments/store', [DepartmentController::class, 'store'])->name('admin.departments.store');
    Route::post('/admin/departments/update/{id}', [DepartmentController::class, 'update'])->name('admin.departments.update');
    Route::get('/admin/departments/delete/{id}', [DepartmentController::class, 'destroy'])->name('admin.departments.delete');
});
