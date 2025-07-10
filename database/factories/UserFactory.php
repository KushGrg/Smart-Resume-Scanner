<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'previously_verified' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with HR role and details.
     */
    public function hr(): self
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('hr');
            $user->hrDetail()->create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => fake()->phoneNumber(),
                'organization_name' => fake()->company(),
                'logo' => null,
            ]);
        });
    }

    /**
     * Create a user with job seeker role and details.
     */
    public function jobSeeker(): self
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('job_seeker');
            $user->jobSeekerDetail()->create([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => fake()->phoneNumber(),
                'current_designation' => fake()->jobTitle(),
                'experience_years' => fake()->randomElement(['0-1', '1-3', '3-5', '5-10', '10+']),
                'skills' => fake()->randomElements([
                    'PHP', 'Laravel', 'JavaScript', 'Vue.js', 'React', 'Python', 'Java', 'C++',
                    'HTML', 'CSS', 'MySQL', 'PostgreSQL', 'MongoDB', 'Git', 'Docker', 'AWS',
                    'Node.js', 'TypeScript', 'Angular', 'Bootstrap', 'Tailwind CSS', 'Redis',
                ], fake()->numberBetween(3, 8)),
                'summary' => fake()->paragraph(3),
            ]);
        });
    }

    /**
     * Create a user with admin role.
     */
    public function admin(): self
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }

    /**
     * Create a user that was previously verified.
     */
    public function previouslyVerified(): self
    {
        return $this->state([
            'previously_verified' => true,
        ]);
    }
}
