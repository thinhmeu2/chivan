<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class LogService
{
    public static function backend(Throwable $e)
    {
        Log::stack(['backend'])->debug($e->getMessage());
    }
    public static function frontend(string|Throwable $e): void
    {
        $mes = is_string($e) ? $e : $e->getMessage();
        Log::stack(['frontend'])->log('error', $mes."\n".request()->url());
    }
    public static function log404(NotFoundHttpException|RouteNotFoundException $e): void
    {
        $detect = new CrawlerDetect();
        $userAgent = request()->header('User-Agent');
        $botName = $detect->isCrawler($userAgent) ? $detect->getMatches() : null;
        Log::stack(['log404'])->log('error',
            url()->current() . "\t"
            . url()->previous() . "\t"
            . ($botName ?: 'Human')
        );
        if (strtolower($botName) == 'googlebot')
            DB::table('url_410')->updateOrInsert(
                ['slug' => trim(resolve(FormatDataService::class)->cleanUrl(url()->current()), '/')],
            );
    }
    public static function hack(MethodNotAllowedHttpException $e): void
    {
        Log::stack(['hack'])->log('warning',
            'Invalid Method: ' . $e->getMessage() . "\n"
            . 'Method: ' . request()->method() . "\n"
            . 'URL: ' . url()->current() . "\n"
            . 'IP: ' . request()->ip()
        );
    }
    public static function sitemap(string|Throwable $e): void
    {
        $message = $e instanceof Throwable ? $e->getMessage() : $e;

        Log::stack(['sitemap'])->warning($message);
    }
    public static function crawl(string|Throwable $e): void
    {
        $message = $e instanceof Throwable ? $e->getMessage() : $e;

        Log::stack(['crawl'])->warning($message);
    }
    public static function revision(string|Throwable $e): void
    {
        $message = $e instanceof Throwable ? $e->getMessage() : $e;

        Log::stack(['revision'])->warning($message);
    }
}
