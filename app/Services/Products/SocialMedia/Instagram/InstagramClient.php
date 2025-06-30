<?php

namespace App\Services\Products\SocialMedia\Instagram;

use App\Jobs\RefreshInstagramTokenJob;
use App\Services\Http\ProxyService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InstagramClient
{
    protected Client $client;
    protected ProxyService $proxyService;
    protected string $tokenPoolCacheKey = 'instagram_csrf_token_pool';
    protected string $workingProxyCacheKey = 'instagram_working_proxy';

    public function __construct(ProxyService $proxyService)
    {
        $this->proxyService = $proxyService;
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
            'proxy' => config('services.proxies.residential'),
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://www.instagram.com/',
            ],
        ]);
    }

    public function sendRequest(string $docId, array $variables): array
    {
        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $token = $this->getTokenFromPool();
            if (!$token) {
                throw new \Exception('CSRF Token Pool is exhausted and could not be refilled.');
            }

            try {
                $response = $this->client->post(config('services.instagram.graphql_url'), [
                    'headers' => ['x-csrftoken' => $token],
                    'form_params' => [
                        'variables' => json_encode($variables),
                        'doc_id' => $docId,
                    ],
                ]);

                $data = json_decode((string)$response->getBody(), true);

                if ($response->getStatusCode() !== 200 || empty($data)) {
                    throw new \Exception('Received an invalid response from Instagram API.');
                }

                return $data;

            } catch (ClientException $e) {
                $statusCode = $e->getResponse()->getStatusCode();

                if ($statusCode === 403) {
                    // 403 means the TOKEN is bad. Remove it from the pool.
                    $this->removeTokenFromPool($token);
                    continue; // Continue to the next iteration to get a new token.
                }

                if ($statusCode === 401) {
                    // 401 likely means a residential PROXY issue.
                    continue; // Continue to the next iteration.
                }

                // For any other client error (e.g., 400 Bad Request), re-throw it immediately as it's not a recoverable error.
                throw $e;
            }
        }

        throw new \Exception("Request failed after trying {$maxAttempts} different tokens.");
    }

    public function refillTokenPool(): array
    {
        Log::info('InstagramClient: Refilling token pool with optimized proxy strategy...');
        $desiredSize = config('services.instagram.token_pool_size', 10);
        $newTokens = [];
        $expiresAt = now()->addWeeks(4)->timestamp;

        // First, quickly try to use a previously cached working proxy.
        if ($workingProxy = Cache::get($this->workingProxyCacheKey)) {
            Log::info('Using cached working proxy to fill the pool.', ['proxy' => $workingProxy]);
            while (count($newTokens) < $desiredSize) {
                try {
                    $token = $this->fetchTokenWithProxy($workingProxy);
                    $newTokens[] = ['token' => $token, 'expires_at' => $expiresAt];
                } catch (\Throwable $e) {
                    Log::warning('Cached working proxy has failed. Forgetting it and starting a new search.', ['proxy' => $workingProxy]);
                    Cache::forget($this->workingProxyCacheKey);
                    break; // Exit this loop to start searching the main list.
                }
            }
        }

        $proxiesToTest = $this->proxyService->getProxies();
        if (empty($proxiesToTest)) {
            throw new \Exception('ProxyService returned no proxies to test.');
        }

        // Loop through the remaining proxies to find a new workhorse and continue filling the pool.
        foreach ($proxiesToTest as $proxy) {
            if (count($newTokens) >= $desiredSize) break;

            try {
                // Try to get the FIRST token from this new proxy.
                $firstToken = $this->fetchTokenWithProxy($proxy);

                // SUCCESS! We found a new working proxy.
                Log::info('Found a new working proxy. Will now use it exclusively.', ['proxy' => $proxy]);
                Cache::put($this->workingProxyCacheKey, $proxy, now()->addWeek());

                $newTokens[] = ['token' => $firstToken, 'expires_at' => $expiresAt];
                while (count($newTokens) < $desiredSize) {
                    try {
                        $token = $this->fetchTokenWithProxy($proxy);
                        $newTokens[] = ['token' => $token, 'expires_at' => $expiresAt];
                    } catch (\Throwable $e) {
                        // This workhorse proxy died mid-fill.
                        Log::warning('The new working proxy failed mid-refill. Searching for another.', ['proxy' => $proxy]);
                        Cache::forget($this->workingProxyCacheKey);
                        break; // Break the inner loop to continue searching the main list.
                    }
                }
            } catch (\Throwable $e) {
                // This proxy is bad, the main foreach loop will simply continue to the next one.
                continue;
            }
        }

        if (!empty($newTokens)) {
            Cache::put($this->tokenPoolCacheKey, $newTokens, now()->addWeeks(5));
            Log::info('Token pool refilled successfully.', ['count' => count($newTokens)]);
        } else {
            Log::error('Failed to fetch any new tokens to refill the pool.');
        }

        return $newTokens;
    }

    protected function getTokenFromPool(): ?string
    {
        $pool = Cache::get($this->tokenPoolCacheKey, []);
        $activePool = array_filter($pool, fn($tokenData) => ($tokenData['expires_at'] ?? 0) > now()->timestamp);

        // If the pool is now empty (or was empty to begin with), trigger the refill process.
        if (empty($activePool)) {
            Log::warning('Token pool is empty or stale. Triggering asynchronous refill.');

            // use a static lock name to prevent a "job stampede".
            $lock = Cache::lock('refilling-instagram-token-pool', 1000);
            if ($lock->get()) {
                Log::info('Lock acquired. Dispatching RefreshInstagramTokenJob to the queue.');
                RefreshInstagramTokenJob::dispatch();
            }

            // Fetch one token immediately for the current request.
            $tokenData = $this->fetchSingleToken();

            if ($tokenData) {
                // add the newly fetched token to the pool.
                $activePool[] = $tokenData;
                Cache::put($this->tokenPoolCacheKey, $activePool, now()->addWeeks(5));
                return $tokenData['token'];
            } else {
                // If we can't even get one token, the request must fail.
                throw new \Exception('Failed to fetch an immediate token after finding an empty pool.');
            }
        }

        // pick a random valid token from the pool.
        $randomIndex = array_rand($activePool);
        return $activePool[$randomIndex]['token'];
    }

    protected function fetchSingleToken(): ?array
    {
        $expiresAt = now()->addWeeks(4)->timestamp;

        // First, try the cached working proxy if it exists
        if ($proxy = Cache::get($this->workingProxyCacheKey)) {
            try {
                $token = $this->fetchTokenWithProxy($proxy);
                return ['token' => $token, 'expires_at' => $expiresAt];
            } catch (\Throwable $th) {
                // The cached proxy has died. Forget it and search for a new one.
                Cache::forget($this->workingProxyCacheKey);
            }
        }

        // Search for a new working proxy from the full list
        $foundProxyData = $this->findAndCacheNewWorkingProxy($this->proxyService->getProxies());
        if ($foundProxyData) {
            return ['token' => $foundProxyData['token'], 'expires_at' => $expiresAt];
        }
        return null;
    }

    private function findAndCacheNewWorkingProxy(array $proxies): ?array
    {
        if (empty($proxies)) return null;
        foreach ($proxies as $index => $proxy) {
            try {
                $token = $this->fetchTokenWithProxy($proxy);
                Cache::put($this->workingProxyCacheKey, $proxy, now()->addDay());
                Log::info('Found a new working proxy.', ['proxy' => $proxy]);
                return ['proxy' => $proxy, 'token' => $token, 'index' => $index];
            } catch (\Throwable $e) {
                continue;
            }
        }
        return null;
    }

    private function fetchTokenWithProxy(string $proxy): string
    {
        $response = (new Client())->get('https://www.instagram.com/accounts/login/', [
            'proxy' => $proxy, 'timeout' => 20, 'verify' => false
        ]);
        if ($response->getStatusCode() === 200) {
            if (preg_match('/"csrf_token":"([^"]+)"/', (string)$response->getBody(), $matches)) {
                return $matches[1];
            }
        }
        throw new \Exception('Could not extract token with proxy: '.$proxy);
    }

    protected function removeTokenFromPool(string $badToken): void
    {
        $pool = Cache::get($this->tokenPoolCacheKey, []);
        $newPool = array_filter($pool, fn($tokenData) => ($tokenData['token'] ?? null) !== $badToken);
        Cache::put($this->tokenPoolCacheKey, array_values($newPool), now()->addWeeks(5));
    }
}
