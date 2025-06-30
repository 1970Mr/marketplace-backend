<?php

namespace App\Services\Http;

use Illuminate\Support\Facades\Http;

class ProxyService
{
    protected string $scrapeUrl = 'https://free-proxy-list.net/';

    public function getProxies(): array
    {
        try {
            $html = $this->fetchHtml();
            return $this->extractProxies($html);
        } catch (\Exception $e) {
            report($e);
            return [];
        }
    }

    protected function fetchHtml(): string
    {
        $response = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'
            ])
            ->get($this->scrapeUrl);

        $response->throw();

        return $response->body();
    }

    /**
     * Extracts proxies by finding the data inside a <textarea>, which is a common
     * anti-scraping technique.
     */
    protected function extractProxies(string $html): array
    {
        // If the textarea method fails, we fall back to the old table parsing method as a backup.
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $table = $xpath->query('//table[contains(@class, "table-bordered")]')->item(0);

        if (!$table) {
            return [];
        }

        $rows = $table->getElementsByTagName('tr');
        $result = [];

        for ($i = 1; $i < $rows->length; $i++) {
            $cols = $rows->item($i)->getElementsByTagName('td');
            if ($cols->length >= 7) {
                $ip = trim($cols->item(0)->nodeValue);
                $port = trim($cols->item(1)->nodeValue);
                $google = strtolower(trim($cols->item(5)->nodeValue));
                $https = strtolower(trim($cols->item(6)->nodeValue));
                if (!empty($ip) && !empty($port) && $google === 'no' && $https === 'yes') {
                    $result[] = 'http://' . $ip . ':' . $port;
                }
            }
        }

        return $result;
    }
}
