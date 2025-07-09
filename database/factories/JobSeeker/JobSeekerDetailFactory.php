<?php

namespace Database\Factories\JobSeeker;

use App\Models\JobSeeker\JobSeekerDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobSeeker\JobSeekerDetail>
 */
class JobSeekerDetailFactory extends Factory
{
    protected $model = JobSeekerDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'current_designation' => fake()->jobTitle(),
            'experience_years' => fake()->randomElement(['0-1', '1-3', '3-5', '5-10', '10+']),
            'skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Python', 'Java', 'C++',
                'HTML', 'CSS', 'MySQL', 'PostgreSQL', 'MongoDB', 'Git', 'Docker', 'AWS',
                'Node.js', 'TypeScript', 'Angular', 'Bootstrap', 'Tailwind CSS', 'Redis',
                'Kubernetes', 'GraphQL', 'REST API', 'Machine Learning', 'Data Analysis',
                'Photoshop', 'Figma', 'UI/UX Design', 'Project Management', 'Agile', 'Scrum',
            ], fake()->numberBetween(3, 8)),
            'summary' => fake()->paragraph(3),
        ];
    }

    /**
     * Create a job seeker detail for an existing user.
     */
    public function forUser(User $user): self
    {
        return $this->state([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    /**
     * Create a fresher job seeker (0-1 years experience).
     */
    public function fresher(): self
    {
        return $this->state([
            'experience_years' => '0-1',
            'current_designation' => fake()->randomElement([
                'Junior Developer',
                'Trainee Software Engineer',
                'Intern',
                'Associate Developer',
                'Graduate Trainee',
            ]),
            'skills' => fake()->randomElements([
                'HTML', 'CSS', 'JavaScript', 'PHP', 'Python', 'Java', 'C++', 'MySQL', 'Git',
            ], fake()->numberBetween(3, 6)),
        ]);
    }

    /**
     * Create an experienced job seeker (5-10 years).
     */
    public function experienced(): self
    {
        return $this->state([
            'experience_years' => fake()->randomElement(['5-10', '10+']),
            'current_designation' => fake()->randomElement([
                'Senior Software Engineer',
                'Tech Lead',
                'Senior Developer',
                'Engineering Manager',
                'Principal Engineer',
                'Architect',
            ]),
            'skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'React', 'Vue.js', 'Node.js', 'Python',
                'AWS', 'Docker', 'Kubernetes', 'MySQL', 'PostgreSQL', 'Redis', 'GraphQL',
                'REST API', 'Microservices', 'System Design', 'Team Leadership',
            ], fake()->numberBetween(6, 12)),
        ]);
    }

    /**
     * Create a designer job seeker.
     */
    public function designer(): self
    {
        return $this->state([
            'current_designation' => fake()->randomElement([
                'UI/UX Designer',
                'Graphic Designer',
                'Product Designer',
                'Visual Designer',
                'Web Designer',
            ]),
            'skills' => fake()->randomElements([
                'Figma', 'Photoshop', 'Illustrator', 'Sketch', 'Adobe XD', 'InVision',
                'UI/UX Design', 'Prototyping', 'Wireframing', 'User Research',
                'Design Systems', 'HTML', 'CSS', 'JavaScript',
            ], fake()->numberBetween(4, 8)),
        ]);
    }

    /**
     * Create a data scientist job seeker.
     */
    public function dataScientist(): self
    {
        return $this->state([
            'current_designation' => fake()->randomElement([
                'Data Scientist',
                'Machine Learning Engineer',
                'Data Analyst',
                'AI Engineer',
                'Research Scientist',
            ]),
            'skills' => fake()->randomElements([
                'Python', 'R', 'SQL', 'Machine Learning', 'Deep Learning', 'TensorFlow',
                'PyTorch', 'Pandas', 'NumPy', 'Scikit-learn', 'Jupyter', 'Tableau',
                'Power BI', 'Statistics', 'Data Visualization', 'Big Data', 'Hadoop', 'Spark',
            ], fake()->numberBetween(6, 10)),
        ]);
    }

    /**
     * Create a job seeker with specific skills.
     */
    public function withSkills(array $skills): self
    {
        return $this->state([
            'skills' => $skills,
        ]);
    }

    /**
     * Create a job seeker with high profile completeness.
     */
    public function completeProfile(): self
    {
        return $this->state([
            'summary' => fake()->paragraphs(3, true),
            'skills' => fake()->randomElements([
                'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Python', 'Java',
                'MySQL', 'PostgreSQL', 'Git', 'Docker', 'AWS', 'REST API',
            ], 8),
        ]);
    }
}
