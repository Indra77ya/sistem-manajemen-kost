<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (!$user || $user->role === 'developer' || $user->role === 'owner') {
            return;
        }

        if ($user->role === 'admin') {
            $branchIds = $user->branches()->pluck('branches.id')->toArray();
            if (empty($branchIds) && $user->branch_id) {
                $branchIds = [$user->branch_id];
            }

            if ($model instanceof \App\Models\Branch) {
                $builder->whereIn('id', $branchIds);
            } elseif ($model instanceof \App\Models\User) {
                $builder->where(function ($query) use ($user, $branchIds) {
                    $query->whereIn('branch_id', $branchIds)
                          ->orWhere('id', $user->id);
                });
            } else {
                $builder->whereIn('branch_id', $branchIds);
            }
        } elseif ($user->role === 'tenant') {
            if ($model instanceof \App\Models\User) {
                $builder->where('id', $user->id);
            } else {
                $builder->where('branch_id', $user->branch_id);
            }
        }
    }
}
