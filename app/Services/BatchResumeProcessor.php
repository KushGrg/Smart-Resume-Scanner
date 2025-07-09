<?php

namespace App\Services;

use App\Jobs\CalculateResumeSimilarity;
use App\Jobs\ProcessResumeText;
use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BatchResumeProcessor
{
    public function __construct(
        private ResumeRanker $ranker
    ) {}

    /**
     * Process all resumes for a job post
     */
    public function processJobPostResumes(JobPost $jobPost): void
    {
        $resumes = $jobPost->resumes()
            ->where('text_extracted', true)
            ->where('processed', false)
            ->get();

        Log::info('Starting batch processing for job post', [
            'job_post_id' => $jobPost->id,
            'resume_count' => $resumes->count(),
        ]);

        $resumes->each(function (Resume $resume) {
            CalculateResumeSimilarity::dispatch($resume);
        });
    }

    /**
     * Process text extraction for multiple resumes
     */
    public function processTextExtraction(Collection $resumes): void
    {
        Log::info('Starting batch text extraction', [
            'resume_count' => $resumes->count(),
        ]);

        $resumes->each(function (Resume $resume) {
            if (! $resume->text_extracted) {
                ProcessResumeText::dispatch($resume);
            }
        });
    }

    /**
     * Recalculate all similarities for a job post (when job description changes)
     */
    public function recalculateJobPostSimilarities(JobPost $jobPost): void
    {
        // Clear existing cache for this job post
        $jobDescription = $this->getJobPostFullText($jobPost);
        $cacheKey = 'job_vector_'.md5($jobDescription);
        Cache::forget($cacheKey);

        $resumes = $jobPost->resumes()
            ->where('text_extracted', true)
            ->get();

        Log::info('Recalculating similarities for job post', [
            'job_post_id' => $jobPost->id,
            'resume_count' => $resumes->count(),
        ]);

        $resumes->each(function (Resume $resume) {
            $resume->update(['processed' => false]);
            CalculateResumeSimilarity::dispatch($resume);
        });
    }

    /**
     * Get ranking statistics for a job post
     */
    public function getJobPostRankingStats(JobPost $jobPost): array
    {
        $resumes = $jobPost->resumes()
            ->where('processed', true)
            ->whereNotNull('similarity_score');

        $scores = $resumes->pluck('similarity_score');

        return [
            'total_resumes' => $resumes->count(),
            'average_score' => $scores->avg() ?? 0,
            'max_score' => $scores->max() ?? 0,
            'min_score' => $scores->min() ?? 0,
            'high_match_count' => $scores->filter(fn ($score) => $score >= 0.7)->count(),
            'medium_match_count' => $scores->filter(fn ($score) => $score >= 0.4 && $score < 0.7)->count(),
            'low_match_count' => $scores->filter(fn ($score) => $score < 0.4)->count(),
        ];
    }

    /**
     * Get top ranked resumes for a job post
     */
    public function getTopRankedResumes(JobPost $jobPost, int $limit = 10): Collection
    {
        return $jobPost->resumes()
            ->where('processed', true)
            ->whereNotNull('similarity_score')
            ->orderBy('similarity_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get resumes by match level
     */
    public function getResumesByMatchLevel(JobPost $jobPost, string $matchLevel): Collection
    {
        $scoreRanges = [
            'high' => [0.7, 1.0],
            'medium' => [0.4, 0.7],
            'low' => [0.0, 0.4],
        ];

        if (! isset($scoreRanges[$matchLevel])) {
            throw new \InvalidArgumentException("Invalid match level: {$matchLevel}");
        }

        [$minScore, $maxScore] = $scoreRanges[$matchLevel];

        return $jobPost->resumes()
            ->where('processed', true)
            ->whereNotNull('similarity_score')
            ->whereBetween('similarity_score', [$minScore, $maxScore])
            ->orderBy('similarity_score', 'desc')
            ->get();
    }

    /**
     * Generate detailed ranking report
     */
    public function generateRankingReport(JobPost $jobPost): array
    {
        $stats = $this->getJobPostRankingStats($jobPost);
        $topResumes = $this->getTopRankedResumes($jobPost, 5);

        return [
            'job_post' => [
                'id' => $jobPost->id,
                'title' => $jobPost->title,
                'created_at' => $jobPost->created_at,
            ],
            'statistics' => $stats,
            'top_resumes' => $topResumes->map(function ($resume) {
                return [
                    'id' => $resume->id,
                    'file_name' => $resume->file_name,
                    'similarity_score' => $resume->similarity_score,
                    'match_level' => $this->getMatchLevel($resume->similarity_score),
                    'processed_at' => $resume->processed_at,
                ];
            }),
            'distribution' => [
                'high_match' => $this->getResumesByMatchLevel($jobPost, 'high')->count(),
                'medium_match' => $this->getResumesByMatchLevel($jobPost, 'medium')->count(),
                'low_match' => $this->getResumesByMatchLevel($jobPost, 'low')->count(),
            ],
        ];
    }

    /**
     * Get processing status for job post resumes
     */
    public function getProcessingStatus(JobPost $jobPost): array
    {
        $totalResumes = $jobPost->resumes()->count();
        $textExtracted = $jobPost->resumes()->where('text_extracted', true)->count();
        $processed = $jobPost->resumes()->where('processed', true)->count();
        $failed = $jobPost->resumes()->where('text_extracted', false)->count();

        return [
            'total_resumes' => $totalResumes,
            'text_extracted' => $textExtracted,
            'processed' => $processed,
            'failed' => $failed,
            'pending_extraction' => $totalResumes - $textExtracted - $failed,
            'pending_processing' => $textExtracted - $processed,
            'completion_percentage' => $totalResumes > 0 ? round(($processed / $totalResumes) * 100, 2) : 0,
        ];
    }

    /**
     * Clear all processing data for a job post
     */
    public function clearJobPostProcessing(JobPost $jobPost): void
    {
        $jobPost->resumes()->update([
            'text_extracted' => false,
            'processed' => false,
            'similarity_score' => null,
            'extracted_text' => null,
            'processed_at' => null,
        ]);

        // Clear cache
        $jobDescription = $this->getJobPostFullText($jobPost);
        $cacheKey = 'job_vector_'.md5($jobDescription);
        Cache::forget($cacheKey);

        Log::info('Cleared processing data for job post', [
            'job_post_id' => $jobPost->id,
        ]);
    }

    /**
     * Get match level based on similarity score
     */
    private function getMatchLevel(float $score): string
    {
        return match (true) {
            $score >= 0.7 => 'high',
            $score >= 0.4 => 'medium',
            default => 'low'
        };
    }

    /**
     * Get job post full text for processing
     */
    private function getJobPostFullText(JobPost $jobPost): string
    {
        return implode(' ', [
            $jobPost->title,
            $jobPost->description,
            $jobPost->requirements,
            $jobPost->location,
            $jobPost->type,
            $jobPost->experience_level,
        ]);
    }
}
