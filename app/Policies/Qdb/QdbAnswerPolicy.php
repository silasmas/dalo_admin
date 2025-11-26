<?php

namespace App\Policies\Qdb;

use App\Models\User;
use App\Models\Qdb\QdbAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class QdbAnswerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_qdb::answer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QdbAnswer $qdbAnswer): bool
    {
        return $user->can('view_qdb::answer');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_qdb::answer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QdbAnswer $qdbAnswer): bool
    {
        return $user->can('update_qdb::answer');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QdbAnswer $qdbAnswer): bool
    {
        return $user->can('delete_qdb::answer');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_qdb::answer');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, QdbAnswer $qdbAnswer): bool
    {
        return $user->can('force_delete_qdb::answer');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_qdb::answer');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, QdbAnswer $qdbAnswer): bool
    {
        return $user->can('restore_qdb::answer');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_qdb::answer');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, QdbAnswer $qdbAnswer): bool
    {
        return $user->can('replicate_qdb::answer');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_qdb::answer');
    }
}
