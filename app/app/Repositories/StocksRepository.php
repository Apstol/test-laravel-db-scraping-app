<?php

namespace App\Repositories;

use App\Exceptions\ApiException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;

class StocksRepository
{
    public function getStocksGenerator(): \Generator
    {
        $currentPage = 1;

        do {
            $url = $this->buildUrl($currentPage);
            $response = Http::get($url);
            $data = $response['data'];
            if (!$data) {
                throw new ApiException('Field "data" not found');
            }

            $lastPage = $response['meta']['last_page'] ?? null;
            if (!$lastPage) {
                throw new ApiException('Field "last_page" not found');
            }

            yield $data;

            Sleep::for(150)->milliseconds();
            ++$currentPage;
        } while ($currentPage <= $lastPage);
    }

    private function buildUrl(int $page): string
    {
        $protocol = Config::get('services.api.protocol');
        $host = Config::get('services.api.host');
        $port = Config::get('services.api.port');
        $dateFrom = Carbon::now()->format('Y-m-d');
        $apiKey = Config::get('services.api.key');

        $url = sprintf("%s://%s:%s/api/stocks?dateFrom=%s&page=%d&key=%s", $protocol, $host, $port, $dateFrom, $page, $apiKey);

        return $url;
    }
}

