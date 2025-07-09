<?php

namespace Database\Factories\Hr;

use App\Models\Hr\HrDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hr\HrDetail>
 */
class HrDetailFactory extends Factory
{
    protected $model = HrDetail::class;

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
            'organization_name' => fake()->company(),
            'logo' => null,
        ];
    }

    /**
     * Create an HR detail for an existing user.
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
     * Create an HR detail with a logo.
     */
    public function withLogo(): self
    {
        return $this->state([
            'logo' => 'logos/'.fake()->uuid().'.png',
        ]);
    }

    /**
     * Create an HR detail for a large organization.
     */
    public function largeOrganization(): self
    {
        return $this->state([
            'organization_name' => fake()->randomElement([
                'Microsoft Corporation',
                'Google LLC',
                'Amazon.com Inc.',
                'Apple Inc.',
                'Meta Platforms Inc.',
                'Tesla Inc.',
                'Netflix Inc.',
                'Adobe Inc.',
                'Salesforce Inc.',
                'Oracle Corporation',
            ]),
        ]);
    }

    /**
     * Create an HR detail for a startup.
     */
    public function startup(): self
    {
        return $this->state([
            'organization_name' => fake()->words(2, true).' '.fake()->randomElement(['Tech', 'Labs', 'Solutions', 'Systems', 'Digital']),
        ]);
    }
}
