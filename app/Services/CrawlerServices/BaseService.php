<?php
namespace App\Services\CrawlerServices;

use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseService
{
    final public function crawler(string $url, string $method = 'GET', array $params = []): Crawler
    {
        $method = strtoupper($method);

        $response = match ($method) {
            'GET' => Http::get($url, $params),
            'POST' => Http::post($url, $params),
            default => throw new \InvalidArgumentException("Unsupported method: {$method}")
        };

        if (!$response->successful()) {
            throw new \RuntimeException("Failed to fetch: {$url} | Status: {$response->status()}");
        }

        // parse JSON
        $json = $response->json();

        if (!isset($json['html'])) {
            throw new \RuntimeException("Invalid response structure: missing 'html'");
        }

        // wrap cho chắc
        $html = '<div id="root">' . $json['html'] . '</div>';

        return new Crawler($html);
    }
}
