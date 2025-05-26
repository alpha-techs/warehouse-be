<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $logId = (string) Str::uuid();
        $request->attributes->set('logId', $logId);
        Log::withContext(['logId' => $logId]);

        $response = $next($request);

        $response->headers->set('X-Log-ID', $logId);

        return $response;
    }
}
