<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if ($role == User::ROLE_ADMIN && !$request->user()->isAdmin()) {
            return redirect('/clients/cards');
        }
        if ($role == User::ROLE_CLIENT && !$request->user()->isClient()) {
            return redirect('/admin/clients');
        }
        return $next($request);
    }
}
