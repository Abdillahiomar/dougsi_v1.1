<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
{
    $user = $request->user();

    if ($user && $user->must_change_password) {
        // Autoriser uniquement la page de changement + déconnexion
        if (! $request->routeIs('password.force', 'password.force.update', 'logout')) {
            return redirect()->route('password.force');
        }
    }

    return $next($request);
}
}
