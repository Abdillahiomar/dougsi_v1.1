<?php

namespace App\Listeners;

use App\Models\AuditLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable;

class RecordAuthActivity
{
    public function handleLogin(Login $event): void
    {
        $this->record('login', $event->guard, $event->user);
    }

    public function handleLogout(Logout $event): void
    {
        $this->record('logout', $event->guard, $event->user);
    }

    private function record(string $type, string $guard, ?Authenticatable $user): void
    {
        if (! $user) {
            return;
        }

        AuditLog::create([
            'guard'      => $guard,
            'event'      => $type,
            'user_id'    => $user->getAuthIdentifier(),
            'user_type'  => $user::class,
            'name'       => $user->name ?? null,
            'email'      => $user->email ?? null,
            'school_id'  => $user->school_id ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
            'created_at' => now(),
        ]);
    }
}
