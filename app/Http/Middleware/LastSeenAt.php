<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LastSeenAt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->id()) {
            auth()->user()->timestamps = false; // Don't update updated_at
            auth()->user()->update([
                'last_seen_at' => DB::raw('now()'),
            ]);
        }

        return $next($request);
    }
}
