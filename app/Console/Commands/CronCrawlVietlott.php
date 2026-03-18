<?php

namespace App\Console\Commands;

use App\Services\FeServices\CategoryService;
use App\Services\LogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CronCrawlVietlott extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cron-crawl-vietlott';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dành cho cronjob 19h01 hàng ngày';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        resolve(CategoryService::class)->todayCategories()
        ->filter(fn($i) => in_array($i->code, config('app.vietlott_codes')))
        ->each(function ($i){
            switch ($i->code){
                case 'MEGA':
                    Artisan::call('crawler:mega --latest');
                    break;
                case 'POWER':
                    Artisan::call('crawler:power --latest');
                    break;
                case 'MAX3D':
                    Artisan::call('crawler:max3d --latest');
                    break;
                case 'MAX3DPRO':
                    Artisan::call('crawler:max3d-pro --latest');
                    break;
                default: LogService::crawl('Nhầm cate:' . $i->code);
            }
        });

    }
}
