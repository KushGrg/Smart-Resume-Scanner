<?php

namespace App\Policies;

use App\Models\JobSeeker\Resume;
use App\Models\User;

class ResumePolicy
{
    /**
     * Determine whether the user can view any resumes.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['hr', 'admin', 'super_admin', 'job_seeker']);
    }

    /**
     * Determine whether the user can view the resume.
     */
    public function view(User $user, Resume $resume): bool
    {
        // Job seekers can view their own resumes
        if ($user->hasRole('job_seeker')) {
            return $resume->jobSeekerDetail->user_id === $user->id;
        }

        // HR can view resumes submitted to their job posts
        if ($user->hasRole('hr')) {
            return $resume->jobPost && $resume->jobPost->user_id === $user->id;
        }

        // Admins can view all resumes
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can create resumes.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('job_seeker');
    }

    /**
     * Determine whether the user can update the resume.
     */
    public function update(User $user, Resume $resume): bool
    {
        // Only job seekers can update their own resumes
        if ($user->hasRole('job_seeker')) {
            return $resume->jobSeekerDetail->user_id === $user->id;
        }

        // Admins can update all resumes (for moderation purposes)
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the resume.
     */
    public function delete(User $user, Resume $resume): bool
    {
        // Job seekers can delete their own resumes
        if ($user->hasRole('job_seeker')) {
            return $resume->jobSeekerDetail->user_id === $user->id;
        }

        // Admins can delete all resumes
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can restore the resume.
     */
    public function restore(User $user, Resume $resume): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the resume.
     */
    public function forceDelete(User $user, Resume $resume): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can download the resume.
     */
    public function download(User $user, Resume $resume): bool
    {
        // Job seekers can download their own resumes
        if ($user->hasRole('job_seeker')) {
            return $resume->jobSeekerDetail->user_id === $user->id;
        }

        // HR can download resumes submitted to their job posts
        if ($user->hasRole('hr')) {
            return $resume->jobPost && $resume->jobPost->user_id === $user->id;
        }

        // Admins can download all resumes
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view resume analytics/scoring.
     */
    public function viewAnalytics(User $user, Resume $resume): bool
    {
        // Job seekers can view analytics for their own resumes
        if ($user->hasRole('job_seeker')) {
            return $resume->jobSeekerDetail->user_id === $user->id;
        }

        // HR can view analytics for resumes submitted to their job posts
        if ($user->hasRole('hr')) {
            return $resume->jobPost && $resume->jobPost->user_id === $user->id;
        }

        // Admins can view all analytics
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can change resume status.
     */
    public function changeStatus(User $user, Resume $resume): bool
    {
        // HR can change status of resumes for their job posts
        if ($user->hasRole('hr')) {
            return $resume->jobPost && $resume->jobPost->user_id === $user->id;
        }

        // Admins can change status of all resumes
        return $user->hasRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can reprocess the resume.
     */
    public function reprocess(User $user, Resume $resume): bool
    {
        // Job seekers can reprocess their own resumes
        if ($user->hasRole('job_seeker')) {
            return $resume->jobSeekerDetail->user_id === $user->id;
        }

        // Admins can reprocess all resumes
        return $user->hasRole(['admin', 'super_admin']);
    }
}
