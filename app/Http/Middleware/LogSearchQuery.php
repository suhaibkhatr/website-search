<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SearchLog;

class LogSearchQuery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/search') && $request->has('q')) {
            $q = trim((string) $request->query('q', ''));
            if ($q !== '') {
                SearchLog::create([
                    'query'   => $q,
                    'user_id' => optional($request->user())->id,
                    'ip'      => $request->ip(),
                    'ua'      => substr((string) $request->userAgent(), 0, 255),
                ]);
            }
        }
        return $next($request);
    }
}
