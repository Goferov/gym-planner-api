<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\PlanUser;
use App\Models\User;

class PlanUserPolicy
{

    public function start(User $user, PlanUser $pu): bool
    {
        return $user->id === $pu->user_id && !$pu->started_at;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['user', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlanUser $pu): bool
    {
        return $user->id === $pu->user_id
            || ($user->role==='trainer' && $pu->plan->trainer_id === $user->id);
    }
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlanUser $planUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlanUser $planUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlanUser $planUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlanUser $planUser): bool
    {
        return false;
    }
}
