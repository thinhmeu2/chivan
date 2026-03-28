<?php

use Illuminate\Support\Facades\Route;

Route::get('soicau', function (){
    (new \App\Keys\DanDe10_3())->init();
    (new \App\Keys\DanDe20_3())->init();
    (new \App\Keys\DanDe30_3())->init();
    (new \App\Keys\De3Cang())->init();
});
Route::get('{slug}', [\App\Http\Controllers\HomeController::class, 'index'])->where('any', '.*');
