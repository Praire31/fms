<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return view("welcome");
})->name("home");

Route::get("/register", [UserController::class, 'registerPage'])->name('user.register');
Route::post("/register", [UserController::class, 'register'])->name('user.store');

Route::get("/login", [UserController::class, 'loginPage'])->name('login');
Route::post("/login", [UserController::class, 'login'])->name('user.login');

// USER
Route::middleware(["auth",'role:user'])->group(function(){
    Route::get("/dashboard", [UserDashboardController::class,"index"])->name("user.dashboard");
});

// ADMIN
Route::middleware(["auth",'role:admin'])->group(function(){
    Route::get("/admin-dashboard", [AdminDashboardController::class,"index"])->name("admin.dashboard");
});