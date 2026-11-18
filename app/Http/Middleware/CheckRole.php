<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek apakah user sudah login dan apakah rolenya ada di dalam parameter
        if ($request->user() && in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        // Jika tidak punya akses, abort 403 atau redirect ke dashboard masing-masing
        abort(403, 'Unauthorized action.');
    }
}
