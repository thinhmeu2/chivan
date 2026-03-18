<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(string $slug): View
    {
        return view('layout', []);
    }
}
