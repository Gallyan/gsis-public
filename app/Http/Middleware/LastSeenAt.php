<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        \App\Models\User::withoutTimestamps(
            function() {
                if(auth()->id()) {
                    auth()->user()->touch('last_seen_at');
                    auth()->user()->save();
                }
            }
        );

        return $next($request);
    }
}
