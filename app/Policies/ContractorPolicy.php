<?php

namespace App\Policies;

use App\Models\Contractor;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Contractor $contractor
     * @return mixed
     */
    public function delete(User $user, Contractor $contractor)
    {
        return $user->role_id === 1;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Contractor $contractor
     * @return mixed
     */
    public function forceDelete(User $user, Contractor $contractor)
    {
        return $user->role_id === 1;
    }
}
