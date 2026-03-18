<?php

use Illuminate\Support\Facades\Route;

Route::get('{slug}', [\App\Http\Controllers\HomeController::class, 'index'])->where('any', '.*');
