<?php

namespace App\Policies\Appt;

use App\Models\User;
use App\Models\Appt\Availability;
use Illuminate\Auth\Access\HandlesAuthorization;

class AvailabilityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_availability');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Availability $availability): bool
    {
        return $user->can('view_availability');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_availability');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Availability $availability): bool
    {
        return $user->can('update_availability');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Availability $availability): bool
    {
        return $user->can('delete_availability');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_availability');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Availability $availability): bool
    {
        return $user->can('force_delete_availability');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_availability');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Availability $availability): bool
    {
        return $user->can('restore_availability');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_availability');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Availability $availability): bool
    {
        return $user->can('replicate_availability');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_availability');
    }
}
