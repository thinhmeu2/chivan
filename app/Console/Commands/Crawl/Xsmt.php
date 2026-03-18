<?php

namespace App\Console\Commands\Crawl;

use App\Console\Commands\BaseCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class Xsmt extends BaseCommand
{
    use CommonCrawlXsmtXsmn;

    protected $signature = 'crawler:xsmt {--date=}';
    protected $description = 'cào ixoso XSMT';

    protected function executeCommand(): void
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->option('date'));
        if (! $date)
            throw new \LogicException("Not valid format date: $date. Valid format: Y-m-d");

        $url = "https://ixoso.com/xsmt-ngay-".$date->format('d-m-Y').".html";

        $crawler = new Crawler(Http::get($url)->body());
        $tableResult = $crawler->filter('.table-result');

        $this->detectTable($tableResult, $date);
    }
}
