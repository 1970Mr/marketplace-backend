<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class OAuthException extends Exception
{
    protected string $errorCode;

    public function __construct(
        string $message = "An OAuth error occurred.",
        string $errorCode = "oauth_error",
        int $code = 400,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => $this->errorCode,
            'message' => __($this->getMessage()),
        ], $this->code);
    }
}
