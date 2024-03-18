<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class LastSeenAt
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->id() && ( auth()->user()->last_seen_at?->diffInSeconds(Carbon::now()) > 60 ) ||
                             is_null(auth()->user()?->last_seen_at) ) {
            \App\Models\User::withoutTimestamps(
                function() {
                        auth()->user()->touch('last_seen_at');
                }
            );
        }

        return $next($request);
    }
}
