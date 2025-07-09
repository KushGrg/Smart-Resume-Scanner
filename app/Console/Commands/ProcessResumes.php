<?php

namespace App\Console\Commands;

use App\Models\Hr\JobPost;
use App\Services\BatchResumeProcessor;
use Illuminate\Console\Command;

class ProcessResumes extends Command
{
    protected $signature = 'resumes:process 
                            {--job-post= : Process resumes for specific job post ID}
                            {--extract-only : Only extract text, don\'t calculate similarity}
                            {--recalculate : Recalculate all similarities}
                            {--clear : Clear all processing data}
                            {--status : Show processing status}';

    protected $description = 'Process resumes for text extraction and similarity calculation';

    public function handle(BatchResumeProcessor $processor): int
    {
        if ($this->option('status')) {
            return $this->showStatus($processor);
        }

        if ($this->option('clear')) {
            return $this->clearProcessing($processor);
        }

        $jobPostId = $this->option('job-post');

        if ($jobPostId) {
            /** @var JobPost|null $jobPost */
            $jobPost = JobPost::find($jobPostId);
            if (! $jobPost) {
                $this->error("Job post with ID {$jobPostId} not found.");

                return 1;
            }

            return $this->processJobPost($jobPost, $processor);
        }

        return $this->processAllResumes($processor);
    }

    private function processJobPost(JobPost $jobPost, BatchResumeProcessor $processor): int
    {
        $this->info("Processing resumes for job post: {$jobPost->title}");

        if ($this->option('recalculate')) {
            $this->info('Recalculating similarities...');
            $processor->recalculateJobPostSimilarities($jobPost);
        } elseif ($this->option('extract-only')) {
            $this->info('Extracting text only...');
            $resumes = $jobPost->resumes()->where('text_extracted', false)->get();
            $processor->processTextExtraction($resumes);
        } else {
            $this->info('Processing resumes...');
            $processor->processJobPostResumes($jobPost);
        }

        $status = $processor->getProcessingStatus($jobPost);
        $this->displayStatus($jobPost, $status);

        return 0;
    }

    private function processAllResumes(BatchResumeProcessor $processor): int
    {
        $jobPosts = JobPost::whereHas('resumes')->get();

        if ($jobPosts->isEmpty()) {
            $this->info('No job posts with resumes found.');

            return 0;
        }

        $this->info("Processing resumes for {$jobPosts->count()} job posts...");

        foreach ($jobPosts as $jobPost) {
            $this->line("Processing: {$jobPost->title}");

            if ($this->option('recalculate')) {
                $processor->recalculateJobPostSimilarities($jobPost);
            } else {
                $processor->processJobPostResumes($jobPost);
            }
        }

        $this->info('Processing jobs dispatched. Check queue status for progress.');

        return 0;
    }

    private function showStatus(BatchResumeProcessor $processor): int
    {
        $jobPosts = JobPost::whereHas('resumes')->get();

        if ($jobPosts->isEmpty()) {
            $this->info('No job posts with resumes found.');

            return 0;
        }

        $this->info('Resume Processing Status Report');
        $this->line(str_repeat('=', 60));

        foreach ($jobPosts as $jobPost) {
            $status = $processor->getProcessingStatus($jobPost);
            $this->displayStatus($jobPost, $status);
            $this->line('');
        }

        return 0;
    }

    private function clearProcessing(BatchResumeProcessor $processor): int
    {
        $jobPostId = $this->option('job-post');

        if ($jobPostId) {
            /** @var JobPost|null $jobPost */
            $jobPost = JobPost::find($jobPostId);
            if (! $jobPost) {
                $this->error("Job post with ID {$jobPostId} not found.");

                return 1;
            }

            if ($this->confirm("Clear all processing data for '{$jobPost->title}'?")) {
                $processor->clearJobPostProcessing($jobPost);
                $this->info("Processing data cleared for job post: {$jobPost->title}");
            }
        } else {
            if ($this->confirm('Clear ALL processing data for ALL job posts?')) {
                $jobPosts = JobPost::whereHas('resumes')->get();
                foreach ($jobPosts as $jobPost) {
                    $processor->clearJobPostProcessing($jobPost);
                }
                $this->info('All processing data cleared.');
            }
        }

        return 0;
    }

    private function displayStatus(JobPost $jobPost, array $status): void
    {
        $this->line("Job Post: {$jobPost->title} (ID: {$jobPost->id})");
        $this->line("Total Resumes: {$status['total_resumes']}");
        $this->line("Text Extracted: {$status['text_extracted']}");
        $this->line("Processed: {$status['processed']}");
        $this->line("Failed: {$status['failed']}");
        $this->line("Completion: {$status['completion_percentage']}%");

        if ($status['completion_percentage'] > 0) {
            $stats = app(BatchResumeProcessor::class)->getJobPostRankingStats($jobPost);
            $this->line('Average Score: '.round($stats['average_score'], 3));
            $this->line("High Matches: {$stats['high_match_count']}");
            $this->line("Medium Matches: {$stats['medium_match_count']}");
            $this->line("Low Matches: {$stats['low_match_count']}");
        }
    }
}
