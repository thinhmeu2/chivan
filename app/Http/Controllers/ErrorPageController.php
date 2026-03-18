<?php

namespace App\Http\Controllers;

use App\Helpers\BreadcrumbHelper;
use App\Helpers\SeoHelper;
use Illuminate\Http\Response;

class ErrorPageController
{
    public function notFound(): Response
    {
        if ($s = request()->get('s')){
            BreadcrumbHelper::add("Tìm kiếm $s", null);
        } else {
            BreadcrumbHelper::add("404", null);
        }
        SeoHelper::setRobots('noindex,nofollow');
        return response()
            ->view('errors.404', compact('s'), 410);
    }
}
