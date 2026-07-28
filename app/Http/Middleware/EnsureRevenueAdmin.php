<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRevenueAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless((int) $request->user()?->id === 1, 403, 'Revenue is available only to the administrator.');
        return $next($request);
    }
}
