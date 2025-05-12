<?php

namespace App\Http\Middleware;

use App\Enums\Acl\RoleType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPanelAccess
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user ||
            !($user->hasRole([RoleType::ADMIN->value, RoleType::SUPER_ADMIN->value]))) {
            abort(403);
        }

        return $next($request);
    }
}
