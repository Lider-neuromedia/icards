<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if (auth()->user()->isClient()) {
                return redirect('/clients/cards');
            }
            if (auth()->user()->isAdmin()) {
                return redirect('/admin/clients');
            }

            return redirect('/home');
        }

        return $next($request);
    }
}
