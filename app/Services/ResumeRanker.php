<?php

namespace App\Services;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResumeRanker
{
    private array $stopWords;

    private array $stemCache = [];

    public function __construct()
    {
        $this->stopWords = $this->getStopWords();
    }

    /**
     * Calculate similarity between resume and job description
     */
    public function calculateSimilarity(Resume $resume, JobPost $jobPost): float
    {
        if (! $resume->text_extracted || ! $resume->extracted_text) {
            throw new \Exception('Resume text not extracted yet');
        }

        $resumeText = $resume->extracted_text;
        $jobDescription = $this->getJobPostFullText($jobPost);

        return $this->calculateTextSimilarity($resumeText, $jobDescription);
    }

    /**
     * Calculate similarity for multiple resumes against a job post (uses TF-IDF)
     */
    public function calculateSimilarityBatch(array $resumes, JobPost $jobPost): array
    {
        $jobDescription = $this->getJobPostFullText($jobPost);

        // Create corpus with job description and all resume texts
        $corpus = [$jobDescription];
        $resumeTexts = [];

        foreach ($resumes as $resume) {
            if ($resume->text_extracted && $resume->extracted_text) {
                $resumeTexts[] = $resume->extracted_text;
                $corpus[] = $resume->extracted_text;
            }
        }

        if (count($corpus) < 2) {
            // Fall back to simple TF similarity if not enough documents
            $results = [];
            foreach ($resumes as $resume) {
                $results[$resume->id] = $this->calculateTextSimilarity(
                    $resume->extracted_text ?? '',
                    $jobDescription
                );
            }

            return $results;
        }

        // Use TF-IDF for larger corpus
        $jobVector = $this->calculateTFIDFVector($jobDescription, $corpus);

        $results = [];
        $index = 0;
        foreach ($resumes as $resume) {
            if ($resume->text_extracted && $resume->extracted_text) {
                $resumeVector = $this->calculateTFIDFVector($resume->extracted_text, $corpus);
                $results[$resume->id] = $this->cosineSimilarity($resumeVector, $jobVector);
            } else {
                $results[$resume->id] = 0.0;
            }
            $index++;
        }

        return $results;
    }

    /**
     * Calculate similarity between two text strings directly
     */
    public function calculateTextSimilarity(string $resumeText, string $jobDescription): float
    {
        if (empty($resumeText) || empty($jobDescription)) {
            return 0.0;
        }

        // Use simple TF vectors first, then enhance with IDF if needed
        $resumeTokens = $this->preprocessText($resumeText);
        $jobTokens = $this->preprocessText($jobDescription);

        if (empty($resumeTokens) || empty($jobTokens)) {
            return 0.0;
        }

        // Create TF vectors (term frequency)
        $resumeVector = $this->createTFVector($resumeTokens);
        $jobVector = $this->createTFVector($jobTokens);

        return $this->cosineSimilarity($resumeVector, $jobVector);
    }

    /**
     * Create term frequency vector
     */
    private function createTFVector(array $tokens): array
    {
        $tokenFreq = array_count_values($tokens);
        $totalTokens = count($tokens);

        $tfVector = [];
        foreach ($tokenFreq as $token => $freq) {
            $tfVector[$token] = $freq / $totalTokens;
        }

        return $tfVector;
    }

    /**
     * Calculate TF-IDF vector for a document
     */
    private function calculateTFIDFVector(string $text, array $corpus): array
    {
        $tokens = $this->preprocessText($text);
        $tokenFreq = array_count_values($tokens);
        $totalTokens = count($tokens);

        if ($totalTokens == 0) {
            return [];
        }

        $tfidfVector = [];

        foreach ($tokenFreq as $token => $freq) {
            // Term Frequency (TF) - normalized
            $tf = $freq / $totalTokens;

            // Inverse Document Frequency (IDF)
            $docCount = $this->getDocumentCount($token, $corpus);

            if ($docCount > 0) {
                $idf = log(count($corpus) / $docCount);
                // TF-IDF Score
                $tfidfVector[$token] = $tf * $idf;
            } else {
                // Term doesn't appear in corpus (shouldn't happen)
                $tfidfVector[$token] = 0;
            }
        }

        return $tfidfVector;
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    private function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        // Get all unique terms from both vectors
        $allTerms = array_unique(array_merge(array_keys($vectorA), array_keys($vectorB)));

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        foreach ($allTerms as $term) {
            $valueA = $vectorA[$term] ?? 0;
            $valueB = $vectorB[$term] ?? 0;

            $dotProduct += $valueA * $valueB;
            $magnitudeA += $valueA * $valueA;
            $magnitudeB += $valueB * $valueB;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Advanced text preprocessing pipeline
     */
    private function preprocessText(string $text): array
    {
        // Convert to lowercase
        $text = strtolower($text);

        // Remove extra whitespaces and normalize
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Remove special characters but keep alphanumeric and spaces
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        // Tokenization
        $tokens = explode(' ', $text);

        // Remove empty tokens and stopwords
        $tokens = array_filter($tokens, function ($token) {
            return ! empty($token) &&
                   strlen($token) > 2 &&
                   ! in_array($token, $this->stopWords);
        });

        // Apply stemming
        $tokens = array_map([$this, 'stem'], $tokens);

        return array_values($tokens);
    }

    /**
     * Basic stemming algorithm for English words
     */
    private function stem(string $word): string
    {
        // Use cache to avoid re-processing same words
        if (isset($this->stemCache[$word])) {
            return $this->stemCache[$word];
        }

        $original = $word;

        // Remove common suffixes
        $suffixes = [
            'ing' => '',
            'ed' => '',
            'er' => '',
            'est' => '',
            'ly' => '',
            'tion' => '',
            'sion' => '',
            'ness' => '',
            'ment' => '',
            'able' => '',
            'ible' => '',
            'ful' => '',
            'less' => '',
        ];

        foreach ($suffixes as $suffix => $replacement) {
            if (strlen($word) > strlen($suffix) + 2 &&
                substr($word, -strlen($suffix)) === $suffix) {
                $word = substr($word, 0, -strlen($suffix)).$replacement;
                break;
            }
        }

        $this->stemCache[$original] = $word;

        return $word;
    }

    /**
     * Get count of documents containing the term
     */
    private function getDocumentCount(string $term, array $corpus): int
    {
        $count = 0;
        foreach ($corpus as $document) {
            $tokens = $this->preprocessText($document);
            if (in_array($term, $tokens)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Combine job post fields into searchable text
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

    /**
     * Batch process resumes for a job post
     */
    public function rankResumesForJobPost(JobPost $jobPost): array
    {
        $resumes = $jobPost->resumes()
            ->where('text_extracted', true)
            ->whereNotNull('extracted_text')
            ->get();

        $rankings = [];

        foreach ($resumes as $resume) {
            try {
                $similarity = $this->calculateSimilarity($resume, $jobPost);

                // Update resume with similarity score
                $resume->update([
                    'similarity_score' => $similarity,
                    'processed' => true,
                    'processed_at' => now(),
                ]);

                $rankings[] = [
                    'resume_id' => $resume->id,
                    'resume' => $resume,
                    'similarity_score' => $similarity,
                    'match_level' => $this->getMatchLevel($similarity),
                ];

            } catch (\Exception $e) {
                Log::error('Failed to calculate similarity for resume', [
                    'resume_id' => $resume->id,
                    'job_post_id' => $jobPost->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Sort by similarity score (highest first)
        usort($rankings, function ($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        return $rankings;
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
     * Get ranking statistics for analysis
     */
    public function getRankingStatistics(array $rankings): array
    {
        $scores = array_column($rankings, 'similarity_score');

        return [
            'total_resumes' => count($rankings),
            'average_score' => count($scores) > 0 ? array_sum($scores) / count($scores) : 0,
            'max_score' => count($scores) > 0 ? max($scores) : 0,
            'min_score' => count($scores) > 0 ? min($scores) : 0,
            'high_match_count' => count(array_filter($scores, fn ($score) => $score >= 0.7)),
            'medium_match_count' => count(array_filter($scores, fn ($score) => $score >= 0.4 && $score < 0.7)),
            'low_match_count' => count(array_filter($scores, fn ($score) => $score < 0.4)),
        ];
    }

    /**
     * English stopwords list
     */
    private function getStopWords(): array
    {
        return [
            'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from',
            'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the',
            'to', 'was', 'will', 'with', 'the', 'this', 'but', 'they', 'have',
            'had', 'what', 'said', 'each', 'which', 'she', 'do', 'how', 'their',
            'if', 'up', 'out', 'many', 'then', 'them', 'these', 'so', 'some',
            'her', 'would', 'make', 'like', 'into', 'him', 'time', 'two', 'more',
            'go', 'no', 'way', 'could', 'my', 'than', 'first', 'been', 'call',
            'who', 'oil', 'sit', 'now', 'find', 'down', 'day', 'did', 'get',
            'come', 'made', 'may', 'part', 'use', 'work', 'way', 'new', 'good',
            'high', 'old', 'see', 'him', 'two', 'how', 'its', 'our', 'out',
            'day', 'get', 'use', 'man', 'new', 'now', 'old', 'see', 'way',
            'who', 'boy', 'did', 'its', 'let', 'put', 'say', 'she', 'too',
            'use',
        ];
    }
}
