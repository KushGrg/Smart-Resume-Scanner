<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view users');
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        // Users can view their own profile
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Admins can view all users
        return $authUser->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create users');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        // Users can update their own profile
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Admins can update all users
        return $authUser->hasPermissionTo('edit users');
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        // Users cannot delete themselves
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // Only admins can delete users
        return $authUser->hasPermissionTo('delete users');
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $targetUser): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        return $user->hasRole(['super_admin']);
    }

    /**
     * Determine whether the user can assign roles to the user.
     */
    public function assignRoles(User $authUser, User $targetUser): bool
    {
        // Users cannot assign roles to themselves
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // Only admins can assign roles
        return $authUser->hasPermissionTo('manage roles');
    }

    /**
     * Determine whether the user can view user's roles and permissions.
     */
    public function viewRoles(User $authUser, User $targetUser): bool
    {
        // Users can view their own roles
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Admins can view all user roles
        return $authUser->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can impersonate the target user.
     */
    public function impersonate(User $authUser, User $targetUser): bool
    {
        // Cannot impersonate yourself
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // Only super admins can impersonate
        if (! $authUser->hasRole('super_admin')) {
            return false;
        }

        // Cannot impersonate other super admins
        return ! $targetUser->hasRole('super_admin');
    }

    /**
     * Determine whether the user can verify email for the target user.
     */
    public function verifyEmail(User $authUser, User $targetUser): bool
    {
        // Users can verify their own email
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Admins can verify any user's email
        return $authUser->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can reset password for the target user.
     */
    public function resetPassword(User $authUser, User $targetUser): bool
    {
        // Users can reset their own password
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Admins can reset any user's password
        return $authUser->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view activity logs for the target user.
     */
    public function viewActivityLogs(User $authUser, User $targetUser): bool
    {
        // Users can view their own activity logs
        if ($authUser->id === $targetUser->id) {
            return true;
        }

        // Admins can view all activity logs
        return $authUser->hasRole(['admin', 'super_admin']);
    }
}
