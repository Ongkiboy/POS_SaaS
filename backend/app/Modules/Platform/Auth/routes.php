<?php

use App\Modules\Platform\Auth\Http\Controllers\RegisterBusinessController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register-business', RegisterBusinessController::class)->name('auth.register-business');
