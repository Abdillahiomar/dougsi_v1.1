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
        // Ne pas interférer avec les requêtes Livewire (mise à jour des composants)
        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        if (! $request->routeIs('password.force', 'logout')) {
            return redirect()->route('password.force');
        }
    }

    return $next($request);
}
}
