<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimezoneMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set timezone for this request
        config(['app.timezone' => 'Asia/Kolkata']);
        date_default_timezone_set('Asia/Kolkata');

        $response = $next($request);

        return $response;
    }
}
