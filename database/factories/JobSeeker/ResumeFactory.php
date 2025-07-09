<?php

namespace Database\Factories\JobSeeker;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\JobSeekerDetail;
use App\Models\JobSeeker\Resume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobSeeker\Resume>
 */
class ResumeFactory extends Factory
{
    protected $model = Resume::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = fake()->firstName().'_'.fake()->lastName().'_Resume.'.fake()->randomElement(['pdf', 'doc', 'docx']);

        return [
            'job_seeker_detail_id' => JobSeekerDetail::factory(),
            'job_post_id' => JobPost::factory(),
            'file_name' => $fileName,
            'file_path' => 'resumes/'.fake()->uuid().'_'.$fileName,
            'file_type' => fake()->randomElement(['pdf', 'doc', 'docx']),
            'file_size' => fake()->numberBetween(50000, 2000000), // 50KB to 2MB
            'extracted_text' => fake()->paragraphs(10, true),
            'text_extracted' => fake()->boolean(85), // 85% chance of being extracted
            'processed' => fake()->boolean(70), // 70% chance of being processed
            'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'similarity_score' => fake()->optional(0.7)->randomFloat(4, 0.1, 1.0),
            'applied_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'application_status' => fake()->randomElement(['pending', 'reviewed', 'shortlisted', 'rejected']),
        ];
    }

    /**
     * Create a resume for specific job seeker and job post.
     */
    public function forApplication(JobSeekerDetail $jobSeeker, JobPost $jobPost): self
    {
        return $this->state([
            'job_seeker_detail_id' => $jobSeeker->id,
            'job_post_id' => $jobPost->id,
        ]);
    }

    /**
     * Create a processed resume with similarity score.
     */
    public function processed(): self
    {
        return $this->state([
            'text_extracted' => true,
            'processed' => true,
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'similarity_score' => fake()->randomFloat(4, 0.3, 1.0),
        ]);
    }

    /**
     * Create an unprocessed resume.
     */
    public function pending(): self
    {
        return $this->state([
            'text_extracted' => false,
            'processed' => false,
            'processed_at' => null,
            'similarity_score' => null,
            'application_status' => 'pending',
        ]);
    }

    /**
     * Create a high-scoring resume.
     */
    public function highScore(): self
    {
        return $this->state([
            'text_extracted' => true,
            'processed' => true,
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'similarity_score' => fake()->randomFloat(4, 0.8, 1.0),
            'application_status' => fake()->randomElement(['reviewed', 'shortlisted']),
        ]);
    }

    /**
     * Create a low-scoring resume.
     */
    public function lowScore(): self
    {
        return $this->state([
            'text_extracted' => true,
            'processed' => true,
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'similarity_score' => fake()->randomFloat(4, 0.1, 0.4),
            'application_status' => fake()->randomElement(['reviewed', 'rejected']),
        ]);
    }

    /**
     * Create a shortlisted resume.
     */
    public function shortlisted(): self
    {
        return $this->state([
            'text_extracted' => true,
            'processed' => true,
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'similarity_score' => fake()->randomFloat(4, 0.7, 1.0),
            'application_status' => 'shortlisted',
        ]);
    }

    /**
     * Create a rejected resume.
     */
    public function rejected(): self
    {
        return $this->state([
            'text_extracted' => true,
            'processed' => true,
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'similarity_score' => fake()->randomFloat(4, 0.1, 0.6),
            'application_status' => 'rejected',
        ]);
    }

    /**
     * Create a PDF resume.
     */
    public function pdf(): self
    {
        return $this->state([
            'file_type' => 'pdf',
            'file_name' => str_replace(['.doc', '.docx'], '.pdf', fake()->firstName().'_'.fake()->lastName().'_Resume.pdf'),
        ]);
    }

    /**
     * Create a Word document resume.
     */
    public function word(): self
    {
        $extension = fake()->randomElement(['doc', 'docx']);

        return $this->state([
            'file_type' => $extension,
            'file_name' => fake()->firstName().'_'.fake()->lastName().'_Resume.'.$extension,
        ]);
    }

    /**
     * Create a recent resume (within last 7 days).
     */
    public function recent(): self
    {
        return $this->state([
            'applied_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a large file resume.
     */
    public function largeFile(): self
    {
        return $this->state([
            'file_size' => fake()->numberBetween(1500000, 5000000), // 1.5MB to 5MB
        ]);
    }

    /**
     * Create a resume with extracted text containing specific keywords.
     */
    public function withKeywords(array $keywords): self
    {
        $text = fake()->paragraphs(8, true).' '.implode(' ', $keywords).' '.fake()->paragraphs(2, true);

        return $this->state([
            'extracted_text' => $text,
            'text_extracted' => true,
        ]);
    }
}
