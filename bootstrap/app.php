<?php

use App\Http\Controllers\ErrorPageController;
use App\Services\LogService;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append([
            HandleCors::class,
            ValidatePostSize::class,
            ConvertEmptyStringsToNull::class,
        ]);

        /*$middleware->alias([
            'auth.default' => CheckAuthenticate::class . ':admin',
        ]);*/

        $middleware->group('web', [
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->reportable(function (Throwable $e) {

            // Composer, migrate, queue, schedule… không có HTTP request
            if (app()->runningInConsole()) {
                return false;
            }

            switch (true) {
                case $e instanceof MethodNotAllowedHttpException:
                    LogService::hack($e);
                    break;

                case in_array(request()->segment(1), ['cms', 'livewire'], true):
                    LogService::backend($e);
                    break;

                default:
                    LogService::frontend($e);
            }

            // false = vẫn cho Laravel xử lý report mặc định
            return false;
        });

        $exceptions->render(function (NotFoundHttpException|RouteNotFoundException $e) {
            // LogService::log404($e);
            return app(ErrorPageController::class)->notFound();
        });
    })
    ->create();
