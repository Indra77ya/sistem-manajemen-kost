<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'owner']);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->hasAnyRole(['super_admin', 'owner']);
    }

    public function create(User $user): bool => false;
    public function update(User $user, Activity $activity): bool => false;
    public function delete(User $user, Activity $activity): bool => false;
}
