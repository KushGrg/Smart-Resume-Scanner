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
     * Calculate similarity with enhanced matching
     */
    public function calculateSimilarity(Resume $resume, JobPost $jobPost): float
    {
        if (!$resume->text_extracted || !$resume->extracted_text) {
            throw new \Exception('Resume text not extracted yet');
        }

        $resumeText = $resume->extracted_text;
        $skillsText = $this->extractSkillsSection($resumeText);

        // Enhanced weights with skill boost
        $weights = [
            'title_description' => 0.25,
            'requirements' => 0.45,  // Increased weight
            'experience' => 0.2,
            'type' => 0.1,
            'exact_skills' => 0.3  // New exact match bonus
        ];

        $scores = [
            'title_description' => $this->calculateTextSimilarity(
                $resumeText,
                $jobPost->title . ' ' . $jobPost->description
            ),
            'requirements' => $this->calculateTextSimilarity(
                $skillsText . ' ' . $resumeText,
                $jobPost->requirements ?? ''
            ),
            'experience' => $this->calculateTextSimilarity(
                $resumeText,
                $jobPost->experience_level ?? ''
            ),
            'type' => $this->calculateTextSimilarity(
                $resumeText,
                $jobPost->type ?? ''
            ),
            'exact_skills' => $this->calculateExactSkillMatch(
                $skillsText,
                $jobPost->requirements ?? ''
            )
        ];

        // Debug logging
        Log::debug('Resume similarity scores', [
            'resume_id' => $resume->id,
            'scores' => $scores,
            'weights' => $weights
        ]);

        // Calculate weighted average (sum can exceed 1.0 due to bonus)
        $totalScore = 0;
        foreach ($scores as $field => $score) {
            $totalScore += $score * $weights[$field];
        }

        return min(1.0, $totalScore); // Cap at 1.0
    }

    /**
     * Calculate exact skill matches (not just TF-IDF)
     */
    private function calculateExactSkillMatch(string $skillsText, string $requirements): float
    {
        $requiredSkills = $this->extractSkillsFromRequirements($requirements);
        $resumeSkills = $this->extractSkillsFromText($skillsText);

        if (empty($requiredSkills))
            return 0;

        $matches = 0;
        foreach ($requiredSkills as $skill) {
            if (in_array($skill, $resumeSkills)) {
                $matches++;
            }
        }

        return $matches / count($requiredSkills);
    }

    /**
     * Extract skills from requirements text
     */
    private function extractSkillsFromRequirements(string $text): array
    {
        $skills = config('resume_matcher.skills', []);
        $pattern = $this->buildSkillsPattern($skills);
        preg_match_all($pattern, $text, $matches);

        return array_unique(array_map('strtolower', $matches[0]));
    }

    /**
     * Extract skills from resume text
     */
    private function extractSkillsFromText(string $text): array
    {
        $skills = config('resume_matcher.skills', []);
        $pattern = $this->buildSkillsPattern($skills);
        preg_match_all($pattern, $text, $matches);

        return array_unique(array_map('strtolower', $matches[0]));
    }

    /**
     * Build regex pattern for skills matching
     */
    private function buildSkillsPattern(array $skills): string
    {
        $patterns = [];
        foreach ($skills as $skill => $aliases) {
            $patterns[] = $skill;
            $patterns = array_merge($patterns, $aliases);
        }

        return '/\b(' . implode('|', array_map('preg_quote', $patterns)) . ')\b/i';
    }

    /**
     * Extract skills section from resume text
     */
    private function extractSkillsSection(string $resumeText): string
    {
        // Look for common skills section headers
        $skillsKeywords = ['technical skills', 'skills', 'competencies'];
        $lines = explode("\n", $resumeText);

        $skillsText = '';
        $inSkillsSection = false;

        foreach ($lines as $line) {
            $line = strtolower(trim($line));

            // Check if line contains skills header
            foreach ($skillsKeywords as $keyword) {
                if (str_contains($line, $keyword)) {
                    $inSkillsSection = true;
                    continue 2;
                }
            }

            if ($inSkillsSection) {
                // Stop at next section header
                if (preg_match('/^[a-z\s]+:$/', $line)) {
                    break;
                }
                $skillsText .= ' ' . $line;
            }
        }

        return $skillsText;
    }

    /**
     * Calculate similarity for multiple resumes against a job post (uses TF-IDF)
     * Now aggregates scores from all relevant fields
     */
    public function calculateSimilarityBatch(array $resumes, JobPost $jobPost): array
    {
        $mainText = $jobPost->title . ' ' . $jobPost->description;
        $requirementsText = $jobPost->requirements ?? '';
        $experienceText = $jobPost->experience_level ?? '';
        $typeText = $jobPost->type ?? '';

        $results = [];
        foreach ($resumes as $resume) {
            if ($resume->text_extracted && $resume->extracted_text) {
                $resumeText = $resume->extracted_text;

                $mainScore = $this->calculateTextSimilarity($resumeText, $mainText);
                $requirementsScore = $this->calculateTextSimilarity($resumeText, $requirementsText);
                $experienceScore = $this->calculateTextSimilarity($resumeText, $experienceText);
                $typeScore = $this->calculateTextSimilarity($resumeText, $typeText);

                $totalScore = ($mainScore + $requirementsScore + $experienceScore + $typeScore) / 4;
                $results[$resume->id] = $totalScore;
            } else {
                $results[$resume->id] = 0.0;
            }
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
            return !empty($token) &&
                strlen($token) > 2 &&
                !in_array($token, $this->stopWords);
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
            if (
                strlen($word) > strlen($suffix) + 2 &&
                substr($word, -strlen($suffix)) === $suffix
            ) {
                $word = substr($word, 0, -strlen($suffix)) . $replacement;
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
     * Combine job post fields into searchable text (legacy, not used for scoring now)
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
            'high_match_count' => count(array_filter($scores, fn($score) => $score >= 0.7)),
            'medium_match_count' => count(array_filter($scores, fn($score) => $score >= 0.4 && $score < 0.7)),
            'low_match_count' => count(array_filter($scores, fn($score) => $score < 0.4)),
        ];
    }

    /**
     * English stopwords list
     */
    private function getStopWords(): array
    {
        return [
            'a',
            'an',
            'and',
            'are',
            'as',
            'at',
            'be',
            'by',
            'for',
            'from',
            'has',
            'he',
            'in',
            'is',
            'it',
            'its',
            'of',
            'on',
            'that',
            'the',
            'to',
            'was',
            'will',
            'with',
            'the',
            'this',
            'but',
            'they',
            'have',
            'had',
            'what',
            'said',
            'each',
            'which',
            'she',
            'do',
            'how',
            'their',
            'if',
            'up',
            'out',
            'many',
            'then',
            'them',
            'these',
            'so',
            'some',
            'her',
            'would',
            'make',
            'like',
            'into',
            'him',
            'time',
            'two',
            'more',
            'go',
            'no',
            'way',
            'could',
            'my',
            'than',
            'first',
            'been',
            'call',
            'who',
            'oil',
            'sit',
            'now',
            'find',
            'down',
            'day',
            'did',
            'get',
            'come',
            'made',
            'may',
            'part',
            'use',
            'work',
            'way',
            'new',
            'good',
            'high',
            'old',
            'see',
            'him',
            'two',
            'how',
            'its',
            'our',
            'out',
            'day',
            'get',
            'use',
            'man',
            'new',
            'now',
            'old',
            'see',
            'way',
            'who',
            'boy',
            'did',
            'its',
            'let',
            'put',
            'say',
            'she',
            'too',
            'use',
        ];
    }
}
