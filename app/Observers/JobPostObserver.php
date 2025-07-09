<?php

namespace App\Observers;

use App\Models\Hr\JobPost;
use Illuminate\Support\Facades\Log;

class JobPostObserver
{
    /**
     * Handle the JobPost "created" event.
     */
    public function created(JobPost $jobPost): void
    {
        Log::info('New job post created', [
            'job_post_id' => $jobPost->id,
            'title' => $jobPost->title,
            'user_id' => $jobPost->user_id,
            'location' => $jobPost->location,
            'type' => $jobPost->type,
        ]);
    }

    /**
     * Handle the JobPost "updated" event.
     */
    public function updated(JobPost $jobPost): void
    {
        // Log status changes
        if ($jobPost->wasChanged('status')) {
            Log::info('Job post status changed', [
                'job_post_id' => $jobPost->id,
                'old_status' => $jobPost->getOriginal('status'),
                'new_status' => $jobPost->status,
            ]);

            // If job post was closed, notify applications
            if ($jobPost->status === 'closed') {
                Log::info('Job post closed, affecting applications', [
                    'job_post_id' => $jobPost->id,
                    'applications_count' => $jobPost->applications_count,
                ]);
            }
        }

        // Log deadline changes
        if ($jobPost->wasChanged('deadline')) {
            Log::info('Job post deadline changed', [
                'job_post_id' => $jobPost->id,
                'old_deadline' => $jobPost->getOriginal('deadline'),
                'new_deadline' => $jobPost->deadline,
            ]);
        }

        // Log salary changes
        if ($jobPost->wasChanged(['salary_min', 'salary_max'])) {
            Log::info('Job post salary range updated', [
                'job_post_id' => $jobPost->id,
                'salary_range' => $jobPost->salary_range,
            ]);
        }
    }

    /**
     * Handle the JobPost "deleted" event.
     */
    public function deleted(JobPost $jobPost): void
    {
        Log::info('Job post deleted', [
            'job_post_id' => $jobPost->id,
            'title' => $jobPost->title,
            'applications_count' => $jobPost->applications_count,
        ]);
    }

    /**
     * Handle the JobPost "restored" event.
     */
    public function restored(JobPost $jobPost): void
    {
        Log::info('Job post restored', [
            'job_post_id' => $jobPost->id,
            'title' => $jobPost->title,
        ]);
    }

    /**
     * Handle the JobPost "force deleted" event.
     */
    public function forceDeleted(JobPost $jobPost): void
    {
        Log::info('Job post permanently deleted', [
            'job_post_id' => $jobPost->id,
            'title' => $jobPost->title,
        ]);
    }
}
