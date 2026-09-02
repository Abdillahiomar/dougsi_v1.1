<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

class SetSpatieTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->school_id) {
            app(PermissionRegistrar::class)
                ->setPermissionsTeamId(Auth::user()->school_id);
        }

        return $next($request);
    }
}