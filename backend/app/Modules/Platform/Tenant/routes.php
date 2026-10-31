<?php

use App\Modules\Platform\Tenant\Http\Controllers\PingController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', PingController::class)->name('platform.ping');
