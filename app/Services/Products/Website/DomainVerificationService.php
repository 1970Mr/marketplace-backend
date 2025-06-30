<?php

namespace App\Services\Products\Website;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * DomainVerificationService is responsible for verifying domain ownership
 * using DNS TXT records via Google's DoH (DNS over HTTPS) API.
 */
class DomainVerificationService
{
    private Client $client;
    private int $maxRetries = 3;
    private int $retryDelay = 1000; // in milliseconds

    /**
     * DomainVerificationService constructor.
     * Initializes the Guzzle HTTP client with default options.
     */
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    /**
     * Verifies the ownership of a domain by checking if the specified UUID
     * exists in the domain's TXT records.
     *
     * @param string $domain The domain or URL to be verified.
     * @param string $uuid The unique identifier to be validated.
     * @return array Verification result with success status, message, and check status.
     */
    public function verifyDomain(string $domain, string $uuid): array
    {
        try {
            // Extract host if URL was passed
            $domain = parse_url($domain, PHP_URL_HOST) ?? $domain;
            if (empty($domain)) {
                return [
                    'success' => false,
                    'error' => 'Domain is empty after sanitization',
                    'is_checked' => true
                ];
            }

            $url = "https://dns.google/resolve?name={$domain}&type=TXT";
            $txtRecords = $this->fetchTxtRecords($url);

            if (empty($txtRecords)) {
                return [
                    'success' => false,
                    'error' => 'No TXT records found for the domain',
                    'is_checked' => true
                ];
            }

            // check uuid
            if (in_array($uuid, $txtRecords, true)) {
                return [
                    'success' => true,
                    'message' => 'Domain successfully verified!',
                    'is_checked' => true
                ];
            }

            return [
                'success' => false,
                'error' => 'Verification code not found in TXT records',
                'is_checked' => true
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to verify domain: ' . $e->getMessage(),
                'is_checked' => false
            ];
        }
    }


    /**
     * Fetches the TXT records of a domain using Google's DNS over HTTPS (DoH) API.
     *
     * @param string $url The DoH API URL for fetching TXT records.
     * @param int $attempt The current retry attempt (default: 1).
     * @return array The list of TXT records.
     * @throws \Exception if the TXT records could not be retrieved after multiple attempts.
     */
    private function fetchTxtRecords(string $url, int $attempt = 1): array
    {
        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);

            if ($data['Status'] !== 0) {
                throw new \Exception('DNS resolution failed via Google DoH');
            }

            $txtRecords = [];
            foreach ($data['Answer'] ?? [] as $record) {
                if ($record['type'] === 16) { // TXT
                    $txtRecords[] = trim($record['data'], '"');
                }
            }

            return $txtRecords;

        } catch (RequestException $e) {
            if ($attempt < $this->maxRetries) {
                // Adding delay between retries
                usleep($this->retryDelay * 1000);
                return $this->fetchTxtRecords($url, $attempt + 1);
            }

            throw new \Exception('Failed to retrieve TXT records after multiple attempts');
        }
    }
}
