<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->utype === 'ADM';
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, User $model)
    {
        return $user->id === $model->id || $user->utype === 'ADM';
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->utype === 'ADM';
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        return $user->id === $model->id || $user->utype === 'ADM';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        // Admin can delete any user except themselves
        // Regular users cannot delete accounts
        return $user->utype === 'ADM' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can update the user's password.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updatePassword(User $user, User $model)
    {
        return $user->id === $model->id || $user->utype === 'ADM';
    }

    /**
     * Determine whether the user can update the user's role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateRole(User $user, User $model)
    {
        // Only admins can change roles, and they cannot change their own role
        return $user->utype === 'ADM' && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can view the user's orders.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewOrders(User $user, User $model)
    {
        return $user->id === $model->id || $user->utype === 'ADM';
    }

    /**
     * Determine whether the user can view the user's addresses.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAddresses(User $user, User $model)
    {
        return $user->id === $model->id || $user->utype === 'ADM';
    }
}
