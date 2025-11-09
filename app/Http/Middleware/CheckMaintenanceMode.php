<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $mode = Setting::where('key', 'mode')->value('value');

        if ($mode === 'on' && !auth()->check()) {
            return response()->view('errors.maintenance', [], 503);
        }

        if ($mode === 'on' && auth()->check() && !auth()->user()->isSuperAdmin('super_admin')) {
            return response()->view('errors.maintenance', [], 503);
        }

        if (auth()->check() && session('locked') === true && !$request->is('admin/lock-screen', 'admin/lock-screen/*')) {
            return redirect()->route('admin.lock.screen');
        }

        return $next($request);
    }
}
