<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class ErrorPageController
{
    public function notFound(): Response
    {
        $s = request()->get('s');
        return response()
            ->view('errors.404', compact('s'), 410);
    }
}
