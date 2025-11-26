<?php

namespace App\Policies\Gallery;

use App\Models\User;
use App\Models\Gallery\GalImage;
use Illuminate\Auth\Access\HandlesAuthorization;

class GalImagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_gal::image');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GalImage $galImage): bool
    {
        return $user->can('view_gal::image');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_gal::image');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GalImage $galImage): bool
    {
        return $user->can('update_gal::image');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GalImage $galImage): bool
    {
        return $user->can('delete_gal::image');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_gal::image');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, GalImage $galImage): bool
    {
        return $user->can('force_delete_gal::image');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_gal::image');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, GalImage $galImage): bool
    {
        return $user->can('restore_gal::image');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_gal::image');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, GalImage $galImage): bool
    {
        return $user->can('replicate_gal::image');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_gal::image');
    }
}
