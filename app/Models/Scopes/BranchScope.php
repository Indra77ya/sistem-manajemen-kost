<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BranchScope implements Scope
{
    protected static $branchIdsCache = [];

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Use hasUser() to avoid triggering auth logic recursively
        if (Auth::hasUser()) {
            $user = Auth::user();

            // Super Admin can see everything
            if ($user->hasRole('super_admin')) {
                return;
            }

            // Owner can also see everything across branches
            if ($user->hasRole('owner')) {
                return;
            }

            // Admin can only see data from branches they are assigned to
            if ($user->hasRole('admin_cabang')) {
                $branchIds = $this->getBranchIds($user);

                if ($model instanceof \App\Models\Announcement) {
                    $builder->where(function ($query) use ($branchIds) {
                        $query->whereIn('branch_id', $branchIds)
                              ->orWhereNull('branch_id');
                    });
                } elseif ($model instanceof \App\Models\Branch) {
                    $builder->whereIn($model->getTable() . '.id', $branchIds);
                } elseif ($model instanceof \App\Models\User) {
                    $builder->where(function ($query) use ($branchIds, $user) {
                        $query->whereHas('branches', function ($q) use ($branchIds) {
                            $q->withoutGlobalScopes()->whereIn('branches.id', $branchIds);
                        })->orWhere($model->getTable() . '.id', $user->id);
                    });
                } else {
                    $builder->whereIn($model->getTable() . '.branch_id', $branchIds);
                }
            }

            // Technician can only see assigned maintenance requests
            if ($user->hasRole('technician')) {
                if ($model instanceof \App\Models\MaintenanceRequest) {
                    $builder->where('technician_id', $user->id);
                } else {
                    // Technician can see branches they are assigned to (if any) or just block other models if not needed
                    // For now, let's treat them like admin for branch visibility if assigned
                    $branchIds = $this->getBranchIds($user);
                    if ($model instanceof \App\Models\Branch) {
                        $builder->whereIn($model->getTable() . '.id', $branchIds);
                    } elseif ($model instanceof \App\Models\User) {
                        $builder->where($model->getTable() . '.id', $user->id);
                    } else {
                        $builder->whereIn($model->getTable() . '.branch_id', $branchIds);
                    }
                }
            }

            // Tenant can only see their own data
            if ($user->hasRole('tenant')) {
                if ($model instanceof \App\Models\Announcement) {
                    $branchIds = $this->getBranchIds($user);
                    $builder->where(function ($query) use ($branchIds) {
                        $query->whereIn('branch_id', $branchIds)
                              ->orWhereNull('branch_id');
                    });
                } elseif ($model instanceof \App\Models\Lease ||
                    $model instanceof \App\Models\MaintenanceRequest) {
                    $builder->where($model->getTable() . '.user_id', $user->id);
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

    protected function getBranchIds(User $user): array
    {
        if (!isset(static::$branchIdsCache[$user->id])) {
            static::$branchIdsCache[$user->id] = $user->branches()
                ->withoutGlobalScopes()
                ->pluck('branches.id')
                ->toArray();
        }

        return static::$branchIdsCache[$user->id];
    }
}
