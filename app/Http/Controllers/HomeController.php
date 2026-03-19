<?php

namespace App\Http\Controllers;

use App\View\Components\SoiCauViet;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(string $slug): View
    {
        /*$component = match ($slug) {
            'soi-cau-viet' => SoiCauViet::class,
        };*/

        return view('layout', [
            'component' => $slug,
        ]);
    }
}
