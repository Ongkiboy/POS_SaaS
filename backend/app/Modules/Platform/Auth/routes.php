<?php

use App\Modules\Platform\Auth\Http\Controllers\LoginController;
use App\Modules\Platform\Auth\Http\Controllers\LogoutController;
use App\Modules\Platform\Auth\Http\Controllers\MeController;
use App\Modules\Platform\Auth\Http\Controllers\RegisterBusinessController;
use Illuminate\Support\Facades\Route;

// Publik (SDD §4.4 API-AUTH-01, API-AUTH-02).
Route::post('/auth/register-business', RegisterBusinessController::class)->name('auth.register-business');
Route::post('/auth/login', LoginController::class)->middleware('throttle:login')->name('auth.login');

// Login (API-AUTH-03). auth:sanctum membaca sesi guard web pada request stateful.
Route::post('/auth/logout', LogoutController::class)->middleware('auth:sanctum')->name('auth.logout');

// API-AUTH-04. Tanpa outlet.context: endpoint ini yang memberi daftar outlet (FR-AUTH-004).
Route::get('/me', MeController::class)->middleware('auth:sanctum')->name('me.show');
