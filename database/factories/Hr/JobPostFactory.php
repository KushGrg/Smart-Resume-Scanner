<?php

namespace Database\Factories\Hr;

use App\Models\Hr\JobPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hr\JobPost>
 */
class JobPostFactory extends Factory
{
    protected $model = JobPost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->randomElement([
            'Software Engineer',
            'Frontend Developer',
            'Backend Developer',
            'Full Stack Developer',
            'DevOps Engineer',
            'Data Scientist',
            'UI/UX Designer',
            'Product Manager',
            'QA Engineer',
            'Mobile App Developer',
            'Machine Learning Engineer',
            'System Administrator',
            'Database Administrator',
            'Technical Lead',
            'Project Manager',
        ]);

        $locations = [
            'Kathmandu, Nepal',
            'Lalitpur, Nepal',
            'Bhaktapur, Nepal',
            'Pokhara, Nepal',
            'Chitwan, Nepal',
            'Remote',
            'Hybrid - Kathmandu',
        ];

        return [
            'user_id' => User::factory()->hr(),
            'title' => $title,
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->randomElement($locations),
            'type' => fake()->randomElement(['full-time', 'part-time', 'remote', 'contract']),
            'deadline' => fake()->optional(0.7)->dateTimeBetween('now', '+60 days'),
            'requirements' => fake()->paragraphs(2, true),
            'experience_level' => fake()->randomElement(['entry', 'mid', 'senior', 'lead']),
            'status' => fake()->randomElement(['active', 'inactive', 'closed']),
            'salary_min' => fake()->optional(0.6)->randomFloat(2, 30000, 80000),
            'salary_max' => fake()->optional(0.6)->randomFloat(2, 50000, 150000),
        ];
    }

    /**
     * Create a job post for an existing user.
     */
    public function forUser(User $user): self
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create an active job post.
     */
    public function active(): self
    {
        return $this->state([
            'status' => 'active',
            'deadline' => fake()->dateTimeBetween('+1 week', '+60 days'),
        ]);
    }

    /**
     * Create an expired job post.
     */
    public function expired(): self
    {
        return $this->state([
            'status' => 'closed',
            'deadline' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Create a remote job post.
     */
    public function remote(): self
    {
        return $this->state([
            'type' => 'remote',
            'location' => 'Remote',
        ]);
    }

    /**
     * Create a job post with salary range.
     */
    public function withSalary(?float $min = null, ?float $max = null): self
    {
        return $this->state([
            'salary_min' => $min ?? fake()->randomFloat(2, 30000, 80000),
            'salary_max' => $max ?? fake()->randomFloat(2, 80000, 150000),
        ]);
    }

    /**
     * Create a software engineering job post.
     */
    public function softwareEngineer(): self
    {
        return $this->state([
            'title' => fake()->randomElement([
                'Software Engineer',
                'Senior Software Engineer',
                'Lead Software Engineer',
                'Principal Software Engineer',
            ]),
            'description' => 'We are looking for a skilled software engineer to join our development team. You will be responsible for designing, developing, and maintaining software applications using modern technologies.',
            'requirements' => 'Bachelor\'s degree in Computer Science or related field. Experience with programming languages such as PHP, Python, JavaScript. Knowledge of databases and web technologies. Strong problem-solving skills.',
            'experience_level' => fake()->randomElement(['mid', 'senior']),
        ]);
    }

    /**
     * Create a frontend developer job post.
     */
    public function frontendDeveloper(): self
    {
        return $this->state([
            'title' => fake()->randomElement([
                'Frontend Developer',
                'Senior Frontend Developer',
                'React Developer',
                'Vue.js Developer',
            ]),
            'description' => 'Join our frontend team to create amazing user experiences. You will work with modern JavaScript frameworks and collaborate closely with designers and backend developers.',
            'requirements' => 'Experience with HTML, CSS, JavaScript. Knowledge of React, Vue.js, or Angular. Understanding of responsive design and cross-browser compatibility. Experience with version control systems.',
            'experience_level' => fake()->randomElement(['entry', 'mid']),
        ]);
    }

    /**
     * Create a data science job post.
     */
    public function dataScientist(): self
    {
        return $this->state([
            'title' => fake()->randomElement([
                'Data Scientist',
                'Senior Data Scientist',
                'Machine Learning Engineer',
                'AI Engineer',
            ]),
            'description' => 'We are seeking a data scientist to analyze complex datasets and build predictive models. You will work with large-scale data and machine learning algorithms to drive business insights.',
            'requirements' => 'Master\'s degree in Data Science, Statistics, or related field. Experience with Python, R, SQL. Knowledge of machine learning frameworks. Strong analytical and statistical skills.',
            'experience_level' => fake()->randomElement(['mid', 'senior']),
        ]);
    }

    /**
     * Create an urgent job post (deadline within 2 weeks).
     */
    public function urgent(): self
    {
        return $this->state([
            'deadline' => fake()->dateTimeBetween('now', '+14 days'),
            'status' => 'active',
        ]);
    }

    /**
     * Create a high-paying job post.
     */
    public function highPaying(): self
    {
        return $this->state([
            'salary_min' => fake()->randomFloat(2, 80000, 120000),
            'salary_max' => fake()->randomFloat(2, 120000, 200000),
            'experience_level' => fake()->randomElement(['senior', 'lead']),
        ]);
    }
}
