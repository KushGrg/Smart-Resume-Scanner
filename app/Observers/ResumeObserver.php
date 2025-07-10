<?php

namespace App\Observers;

use App\Jobs\CalculateResumeSimilarity;
use App\Jobs\ProcessResumeText;
use App\Models\JobSeeker\Resume;
use Illuminate\Support\Facades\Log;
use Storage;

class ResumeObserver
{
    /**
     * Handle the Resume "created" event.
     */
    public function created(Resume $resume): void
    {
        Log::info('New resume uploaded', [
            'resume_id' => $resume->id,
            'job_seeker_id' => $resume->job_seeker_detail_id,
            'job_post_id' => $resume->job_post_id,
            'file_name' => $resume->file_name,
        ]);

        // Dispatch text extraction job if file exists and text hasn't been extracted
        if ($resume->file_path && ! $resume->text_extracted) {
            // In a real application, you would dispatch a job here
            ProcessResumeText::dispatch($resume);
            Log::info('Text extraction job would be dispatched for resume', ['resume_id' => $resume->id]);
        }
    }

    /**
     * Handle the Resume "updated" event.
     */
    public function updated(Resume $resume): void
    {
        // If text was extracted, calculate similarity
        if ($resume->wasChanged('text_extracted') && $resume->text_extracted && ! $resume->processed) {
            // In a real application, you would dispatch a job here
            CalculateResumeSimilarity::dispatch($resume);
            Log::info('Similarity calculation job would be dispatched for resume', ['resume_id' => $resume->id]);
        }

        // Log status changes
        if ($resume->wasChanged('application_status')) {
            Log::info('Resume application status changed', [
                'resume_id' => $resume->id,
                'old_status' => $resume->getOriginal('application_status'),
                'new_status' => $resume->application_status,
            ]);
        }

        // Log similarity score updates
        if ($resume->wasChanged('similarity_score')) {
            Log::info('Resume similarity score updated', [
                'resume_id' => $resume->id,
                'similarity_score' => $resume->similarity_score,
                'score_percentage' => $resume->score_percentage,
            ]);
        }
    }

    /**
     * Handle the Resume "deleted" event.
     */
    public function deleted(Resume $resume): void
    {
        Log::info('Resume deleted', [
            'resume_id' => $resume->id,
            'file_path' => $resume->file_path,
        ]);

        // In a real application, you might want to delete the physical file
        Storage::delete($resume->file_path);
    }

    /**
     * Handle the Resume "restored" event.
     */
    public function restored(Resume $resume): void
    {
        Log::info('Resume restored', [
            'resume_id' => $resume->id,
        ]);
    }

    /**
     * Handle the Resume "force deleted" event.
     */
    public function forceDeleted(Resume $resume): void
    {
        Log::info('Resume force deleted', [
            'resume_id' => $resume->id,
            'file_path' => $resume->file_path,
        ]);

        // In a real application, you would definitely delete the physical file here
        Storage::delete($resume->file_path);
    }
}
