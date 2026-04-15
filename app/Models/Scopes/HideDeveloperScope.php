<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class HideDeveloperScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // If we are not logged in, we are likely trying to log in.
        // We should allow finding the user by email including developers.
        if (!Auth::check() && !app()->runningInConsole()) {
            return;
        }

        // If logged in as developer, show everything.
        if (Auth::check() && Auth::user()->role === 'developer') {
            return;
        }

        // If in console, show everything (seeds, etc)
        if (app()->runningInConsole()) {
            return;
        }

        $builder->where('role', '!=', 'developer');
    }
}
