<?php

namespace App\Policies;

use App\Models\CheckList;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckListPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, CheckList $checkList): bool
    {
        return $user->role_id === 1;
    }

}
