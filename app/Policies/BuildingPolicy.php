<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuildingPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Building $building): bool
    {
        return $user->role_id === 1;
    }

}
