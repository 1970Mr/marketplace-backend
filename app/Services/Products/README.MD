# Product Verification Services

This project provides product verification services for YouTube channels, Instagram accounts, TikTok profiles, and web domains. The current version includes the following verification methods:

-----

## Verification Methods

-----

### 1\. YouTube Channel Verification

Verification through Google OAuth using the YouTube Data API.

#### Features:

* OAuth-based secure authentication.
* Token management with refresh support.
* Automatic channel info retrieval.

#### API Endpoints:

* **POST** `/youtube-channel/verify` - Verify a YouTube channel by its ID.
* **GET** `/auth/google` - Redirect to Google login page for OAuth.
* **GET** `/auth/google/callback` - Handle the OAuth callback after user authentication.

#### Configuration:

Ensure the following environment variables are set in your `.env` file:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://your-app.com/auth/google/callback
```

#### Google Client Configuration (`config/services.php`):

```php
'google' => [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect'      => env('GOOGLE_REDIRECT_URI'),
],
```

#### Routes:

```php
Route::post('/youtube-channel/verify', [YoutubeChannelController::class, 'verify'])->middleware('auth:sanctum');
Route::get('/auth/google', [YoutubeAuthController::class, 'redirectToGoogle'])->middleware('auth:sanctum');
Route::get('/auth/google/callback', [YoutubeAuthController::class, 'handleGoogleCallback'])->middleware('auth:sanctum');
```

#### Usage:

1.  Make a `GET` request to `/auth/google` to get the Google OAuth URL.
2.  Redirect the user to the returned URL for authentication.
3.  On successful authorization, the user will be redirected back to your app via the configured callback.
4.  Use the `POST /youtube-channel/verify` endpoint with the channel ID to complete the verification process.

-----

### 2\. Instagram Account Verification

This service verifies an Instagram account by checking for a unique code in the user's profile bio. It uses a highly resilient, multi-layered architecture designed for high performance and fault tolerance when interacting with Instagram's GraphQL API.

#### Architecture & Logic Flow

The system is designed to be fast for users and robust in the background. It separates the immediate needs of a user request from the slow, proactive maintenance tasks.

**A. User Request Flow (Non-Blocking)**

1.  An incoming request to verify an account calls the `InstagramVerificationService`.
2.  The service uses the `InstagramClient` to make API calls (e.g., `searchUser`).
3.  The `InstagramClient` requests a CSRF token from its internal token pool, which is managed in the Laravel Cache.
4.  **Happy Path:** It takes a random, non-expired token from the pool. If the API request fails with a `403` (bad token) or `401` (proxy auth issue), it automatically retries the request up to 3 times with different tokens from the pool.
5.  **Empty Cache Path:** If the token pool is empty or all tokens have expired, the client does **not** block the user. It fetches just **one** single token for the immediate request while simultaneously dispatching a **background job** to refill the entire pool for future requests. A cache lock mechanism ensures this background job is only dispatched once, even under heavy load.

**B. Background Maintenance Flow (Proactive & Monitored)**

1.  The Laravel Scheduler triggers the `RefreshInstagramTokenJob` to run on a set schedule (e.g., every two weeks).
2.  The job executes the `refillTokenPool` method on the `InstagramClient`.
3.  This method intelligently scrapes a list of free public proxies, finds one that works, and then reuses that single working proxy to efficiently build a new pool of fresh tokens.
4.  Each new token is stored with its own individual 4-week expiration date.
5.  If the job fails to refill the pool for any reason, it automatically sends a **detailed failure notification via email** to the administrator, ensuring problems are addressed before they impact users.

#### Configuration

Ensure the following environment variables and service configurations are set up.

**Environment Variables (`.env` file):**

```env
# The full URL for the residential proxy used for critical Instagram API calls.
PROXY_RESIDENTIAL_URL=your-residential-proxy-url

# The email address for receiving failure notifications from the scheduled job.
ADMIN_EMAIL=your-admin-email@example.com
```

**Service Configuration (`config/services.php`):**

```php
'instagram' => [
'graphql_url'      => 'https://www.instagram.com/graphql/query',
'search_doc_id'    => '29271788265799575',
'profile_doc_id'   => '9718997071514355',
'token_pool_size'  => 10, // The desired number of tokens to maintain in the pool.
],

'proxies' => [
'residential' => env('PROXY_RESIDENTIAL_URL'),
],

'admin_notification_email' => env('ADMIN_EMAIL'),
```

#### Endpoint

```http
POST /instagram-account/{product:uuid}/verify
```

#### Service Breakdown

* **`InstagramVerificationService`**: The top-level service that orchestrates the business logic (`searchUser`, `getProfile`, `verifyAccount`).
* **`InstagramClient`**: The core workhorse. It manages the token pool, uses the residential proxy for API calls, and contains all retry and error-handling logic.
* **`ProxyService`**: A utility service responsible for scraping lists of free proxies for the token refill process.
* **`RefreshInstagramTokenJob`**: A queued job that runs on a schedule to proactively refill the token pool.
* **`InstagramTokenRefreshFailed`**: A notification class that formats and sends the failure alert email to the admin.

#### Caching and Proactive Management

The system's resilience comes from its advanced caching and job-based management:

* **Token Pool:** Instead of a single token, a collection of tokens is cached.
* **Per-Token TTL:** Each token has its own 4-week lifespan, ensuring the pool stays fresh.
* **Asynchronous Refills:** User requests are never blocked by the slow process of finding new tokens.
* **Scheduled Jobs:** A cron-triggered job ensures the token pool is always healthy, creating a self-healing and low-maintenance system.

-----

### 3\. TikTok Account Verification

This section provides an overview of the TikTok account verification functionality. The verification process ensures that users can only connect their TikTok accounts if the specified UUID is present in their TikTok profile bio.

#### Route Definition

TikTok account verification is handled via a POST route:

```php
Route::post('/tiktok-account/{product:uuid}/verify', [TiktokAccountController::class, 'verify'])->middleware('auth:sanctum');
```

#### Service

* **fetchProfileHtml** - Fetches the TikTok profile HTML.
* **extractUuidFromBio** - Extracts the UUID from the bio section of the TikTok profile.
* **verifyAccount** - Verifies if the provided UUID is present in the bio of the specified TikTok account.

#### Notes:

* The `fetchProfileHtml()` method disables SSL verification for faster processing, but this can be adjusted for more secure connections.
* Ensure the TikTok profile URL is valid and accessible to avoid unnecessary exceptions.

-----

### 4\. Domain Verification

The domain verification module provides an efficient way to verify ownership of a domain through DNS TXT records. This process ensures that a domain owner has control over the domain by checking for a specific verification code (UUID) in the DNS records.

#### Domain Verification Endpoint

```plaintext
POST /domain/{product:uuid}/verify
```

Verifies the domain associated with a given product. Requires authentication via the `auth:sanctum` middleware.

#### DomainController

Responsible for handling the domain verification logic and returning appropriate responses.

#### DomainVerificationService

Handles the core logic of verifying the domain via DNS TXT records using Google's DNS over HTTPS (DoH) service.

**Methods:**

* **verifyDomain** - Validates the ownership of a domain by checking if a specified UUID exists in the domain's TXT records.
* **fetchTxtRecords** - Retrieves the TXT records of a domain using the DoH API.

#### Response Format

Returns a JSON response indicating the success or failure of the verification.

**Success Example:**

```json
{
    "success": true,
    "message": "Domain successfully verified!",
    "is_checked": true
}
```

**Failure Example:**

```json
{
    "success": false,
    "error": "Verification code not found in TXT records",
    "is_checked": true
}
```

**Notes:**

* If `is_checked` is `false`, it means you need to try again because the verification attempt did not receive a definitive response to confirm or reject the domain.

-----

### General Notes

* All endpoints require authentication via Laravel Sanctum (`auth:sanctum`).
* Handle exceptions gracefully at the controller level for better error messages.
* Consider adding rate limiting and caching to improve performance and prevent abuse.

-----
