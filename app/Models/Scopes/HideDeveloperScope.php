<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HideDeveloperScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Only hide if the user is authenticated and is NOT a developer
        // During login, Auth::check() is false, so we should NOT hide developers
        // otherwise they can't login.
        if (Auth::check() && Auth::user()->role !== User::ROLE_DEVELOPER) {
            $builder->where('role', '!=', User::ROLE_DEVELOPER);
        }
    }
}
