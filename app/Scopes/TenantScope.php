<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Le superadmin n'est pas un tenant → il voit toutes les écoles
        if (auth('superadmin')->check()) {
            return;
        }
        if (Auth::check() && Auth::user()->school_id) {
            $builder->where($model->getTable() . '.school_id', Auth::user()->school_id);
        }
    }
}
