<?php

namespace App\Jobs;

use App\Models\JobSeeker\Resume;
use App\Services\TextExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessResumeText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 3;

    public function __construct(
        private Resume $resume
    ) {}

    public function handle(TextExtractionService $textExtractor): void
    {
        try {
            Log::info('Starting text extraction for resume', [
                'resume_id' => $this->resume->id,
                'file_name' => $this->resume->file_name,
            ]);

            // Validate file before processing
            $filePath = storage_path('app/public/'.$this->resume->file_path);
            $textExtractor->validateResumeFile($filePath, $this->resume->file_type);

            // Extract text from resume
            $extractedText = $textExtractor->extractTextFromResume($this->resume);

            // Get text statistics
            $stats = $textExtractor->getTextStatistics($extractedText);

            Log::info('Text extraction completed', [
                'resume_id' => $this->resume->id,
                'text_length' => $stats['character_count'],
                'word_count' => $stats['word_count'],
                'readability_score' => $stats['readability_score'],
            ]);

            // Dispatch similarity calculation job
            CalculateResumeSimilarity::dispatch($this->resume);

        } catch (\Exception $e) {
            Log::error('Resume text extraction failed', [
                'resume_id' => $this->resume->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark resume as failed
            $this->resume->update([
                'text_extracted' => false,
                'processed' => false,
                'extracted_text' => null,
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Resume text extraction job failed permanently', [
            'resume_id' => $this->resume->id,
            'error' => $exception->getMessage(),
        ]);

        $this->resume->update([
            'text_extracted' => false,
            'processed' => false,
            'extracted_text' => null,
        ]);
    }
}
