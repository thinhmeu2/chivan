<?php

use App\Keys\DanDe10_3;
use App\Keys\DanDe20_3;
use App\Keys\DanDe30_3;
use App\Keys\De3Cang;
use Illuminate\Support\Facades\Route;

Route::get('soicau', function (){
    (new DanDe10_3())->init();
//    (new DanDe20_3())->init();
//    (new DanDe30_3())->init();
//    (new De3Cang())->init();
});
Route::get('{slug}', [\App\Http\Controllers\HomeController::class, 'index'])->where('any', '.*');
