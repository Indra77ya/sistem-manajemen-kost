<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class HideDeveloperScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Use hasUser() to avoid triggering recursion during user resolution
        if (Auth::hasUser() && Auth::user()->role === 'developer') {
            return;
        }

        if (app()->runningInConsole()) {
            return;
        }

        $builder->where('role', '!=', 'developer');
    }
}
