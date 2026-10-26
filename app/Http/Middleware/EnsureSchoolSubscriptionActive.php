<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolSubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->school_id) {
            // Ne pas interférer avec les requêtes Livewire (mise à jour des
            // composants) — même choix que EnsurePasswordChanged.
            if ($request->hasHeader('X-Livewire')) {
                return $next($request);
            }

            if (
                ! $request->routeIs('subscription.expired', 'logout')
                && SubscriptionAccessService::isBlocked($user->school)
            ) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
