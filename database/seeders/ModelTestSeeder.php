<?php

namespace Database\Seeders;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ModelTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = ['hr', 'job_seeker', 'admin'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create HR users with job posts
        $hrUsers = User::factory(5)->hr()->create();

        // Create some job posts for HR users
        foreach ($hrUsers as $hrUser) {
            JobPost::factory(3)->forUser($hrUser)->create();
        }

        // Create job seeker users
        $jobSeekers = User::factory(10)->jobSeeker()->create();

        // Create some resumes for job seekers
        $jobPosts = JobPost::all();
        foreach ($jobSeekers as $jobSeeker) {
            // Each job seeker applies to 1-3 jobs
            $randomJobPosts = $jobPosts->random(rand(1, 3));
            foreach ($randomJobPosts as $jobPost) {
                Resume::factory()->forApplication($jobSeeker->jobSeekerDetail, $jobPost)->create();
            }
        }

        $this->command->info('✅ Model test data seeded successfully!');
        $this->command->info("📊 Created: {$hrUsers->count()} HR users, {$jobSeekers->count()} job seekers, {$jobPosts->count()} job posts");
    }
}
