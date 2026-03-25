<?php

use Illuminate\Support\Facades\Route;

Route::get('soicau', function (){
    (new \App\Keys\DanDe10_3())->init();
});
Route::get('{slug}', [\App\Http\Controllers\HomeController::class, 'index'])->where('any', '.*');
