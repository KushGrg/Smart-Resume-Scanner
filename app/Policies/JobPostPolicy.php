<?php

namespace App\Policies;

use App\Models\Hr\JobPost;
use App\Models\User;

class JobPostPolicy
{
    /**
     * Determine whether the user can view any job posts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view job posts');
    }

    /**
     * Determine whether the user can view the job post.
     */
    public function view(User $user, JobPost $jobPost): bool
    {
        // Job seekers can view active job posts
        if ($user->hasRole('job_seeker')) {
            return $jobPost->status === 'active';
        }

        // HR can view their own job posts
        if ($user->hasRole('hr')) {
            return $jobPost->user_id === $user->id;
        }

        // Admins can view all job posts
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can create job posts.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create job posts');
    }

    /**
     * Determine whether the user can update the job post.
     */
    public function update(User $user, JobPost $jobPost): bool
    {
        // HR can edit their own job posts
        if ($user->hasRole('hr')) {
            return $jobPost->user_id === $user->id;
        }

        // Admins can edit all job posts
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the job post.
     */
    public function delete(User $user, JobPost $jobPost): bool
    {
        // HR can delete their own job posts (if not having applications)
        if ($user->hasRole('hr')) {
            return $jobPost->user_id === $user->id &&
                   $jobPost->resumes()->count() === 0;
        }

        // Admins can delete all job posts
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can restore the job post.
     */
    public function restore(User $user, JobPost $jobPost): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the job post.
     */
    public function forceDelete(User $user, JobPost $jobPost): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view applications for the job post.
     */
    public function viewApplications(User $user, JobPost $jobPost): bool
    {
        // HR can view applications for their job posts
        if ($user->hasRole('hr')) {
            return $jobPost->user_id === $user->id;
        }

        // Admins can view all applications
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can apply to the job post.
     */
    public function apply(User $user, JobPost $jobPost): bool
    {
        // Only job seekers can apply
        if (! $user->hasRole('job_seeker')) {
            return false;
        }

        // Job must be active
        if ($jobPost->status !== 'active') {
            return false;
        }

        // Check if deadline has passed
        if ($jobPost->deadline && $jobPost->deadline < now()) {
            return false;
        }

        // Check if user has already applied
        return ! $jobPost->resumes()
            ->whereHas('jobSeekerDetail', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->exists();
    }

    /**
     * Determine whether the user can close/open the job post.
     */
    public function changeStatus(User $user, JobPost $jobPost): bool
    {
        // HR can change status of their own job posts
        if ($user->hasRole('hr')) {
            return $jobPost->user_id === $user->id;
        }

        // Admins can change status of all job posts
        return $user->hasRole(['admin', 'super_admin']);
    }
}
