<?php

namespace App\Http\Middleware;

use App\Events\UserOnlineStatusChanged;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            broadcast(new UserOnlineStatusChanged(auth()->id(), true));
        }

        return $next($request);
    }
}
