<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class MemberPolicy
{

    public function viewAny(User $user): bool
    {

        return in_array($user->role, ['coordinator', 'team_lead']);
    }


    public function view(User $user, User $member): bool
    {
        return true;
    }

    public function update(User $user, User $member): bool
    {

        if ($user->role === 'coordinator') {
            return true;
        }


        return $user->id === $member->id;
    }


    public function delete(User $user, User $member): bool
    {
        
        return $user->role === 'coordinator';
    }
}
