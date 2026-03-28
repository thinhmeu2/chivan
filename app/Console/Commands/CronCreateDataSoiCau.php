<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CronCreateDataSoiCau extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cron-create-data-soi-cau';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new \App\Keys\DanDe10_3())->init();
        (new \App\Keys\DanDe20_3())->init();
        (new \App\Keys\DanDe30_3())->init();
        (new \App\Keys\De3Cang())->init();
    }
}
