<?php

namespace App\Jobs;

use App\Models\JobSeeker\Resume;
use App\Services\ResumeRanker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateResumeSimilarity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes

    public int $tries = 2;

    public function __construct(
        private Resume $resume
    ) {}

    public function handle(ResumeRanker $ranker): void
    {
        try {
            if (! $this->resume->text_extracted || ! $this->resume->extracted_text) {
                throw new \Exception('Resume text not extracted yet');
            }

            $jobPost = $this->resume->jobPost;
            if (! $jobPost) {
                throw new \Exception('Job post not found for resume');
            }

            Log::info('Calculating similarity for resume', [
                'resume_id' => $this->resume->id,
                'job_post_id' => $jobPost->id,
            ]);

            $similarity = $ranker->calculateSimilarity($this->resume, $jobPost);

            $this->resume->update([
                'similarity_score' => $similarity,
                'processed' => true,
                'processed_at' => now(),
            ]);

            Log::info('Similarity calculated successfully', [
                'resume_id' => $this->resume->id,
                'job_post_id' => $jobPost->id,
                'similarity_score' => $similarity,
            ]);

        } catch (\Exception $e) {
            Log::error('Similarity calculation failed', [
                'resume_id' => $this->resume->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Similarity calculation job failed permanently', [
            'resume_id' => $this->resume->id,
            'error' => $exception->getMessage(),
        ]);

        $this->resume->update([
            'processed' => false,
            'similarity_score' => null,
        ]);
    }
}
