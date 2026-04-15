<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Developer and Owner can see everything
            if ($user->role === User::ROLE_DEVELOPER || $user->role === User::ROLE_OWNER) {
                return;
            }

            // Admin can only see data from branches they are assigned to
            if ($user->role === User::ROLE_ADMIN) {
                $branchIds = $user->branches->pluck('id')->toArray();

                if ($model instanceof \App\Models\Branch) {
                    $builder->whereIn('id', $branchIds);
                } elseif ($model instanceof \App\Models\User) {
                    $builder->whereHas('branches', function ($query) use ($branchIds) {
                        $query->whereIn('branches.id', $branchIds);
                    })->orWhere('id', $user->id);
                } else {
                    $builder->whereIn('branch_id', $branchIds);
                }
            }

            // Tenant can only see their own data
            if ($user->role === User::ROLE_TENANT) {
                if ($model instanceof \App\Models\Lease ||
                    $model instanceof \App\Models\MaintenanceRequest) {
                    $builder->where('user_id', $user->id);
                } elseif ($model instanceof \App\Models\Invoice) {
                    $builder->whereHas('lease', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                } elseif ($model instanceof \App\Models\Payment) {
                    $builder->whereHas('invoice', function ($query) use ($user) {
                        $query->whereHas('lease', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                    });
                } elseif ($model instanceof \App\Models\Branch) {
                    $builder->whereHas('users', function ($query) use ($user) {
                        $query->where('users.id', $user->id);
                    });
                } elseif ($model instanceof \App\Models\Room) {
                    $builder->whereHas('leases', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                }
            }
        }
    }
}
