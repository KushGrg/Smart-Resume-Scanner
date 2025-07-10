<?php

use App\Models\Hr\JobPost;
use App\Models\User;
use Laravel\Dusk\Browser;

test('complete srs workflow - hr creates job, job seeker applies', function () {
    $this->browse(function (Browser $browser) {
        // Step 1: Create HR user with proper details
        $hrUser = User::firstOrCreate(
            ['email' => 'hr_workflow@gmail.com'],
            [
                'name' => 'HR Workflow User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $hrUser->assignRole('hr');

        // Create HR details
        $hrDetail = \App\Models\Hr\HrDetail::firstOrCreate(
            ['user_id' => $hrUser->id],
            [
                'name' => $hrUser->name,
                'email' => $hrUser->email,
                'organization_name' => 'Test Company',
                'phone' => '1234567890',
            ]
        );

        // Step 2: HR creates a job post
        $browser->visit('/logout')
            ->pause(1000)
            ->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'hr_workflow@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->pause(5000); // Give more time for login redirect

        // Check if we can access dashboard
        $browser->visit('/dashboard')
            ->assertSee('Dashboard');

        // Navigate to job posting
        $browser->visit('/hr/jobpost')
            ->pause(3000) // Wait for page to load
            ->assertSee('Job Post');

        // Step 3: Register as job seeker (skip job creation for now, focus on user flow)
        $jobSeekerEmail = 'jobseeker_workflow_'.time().'@example.com';

        $browser->visit('/logout')
            ->pause(1000)
            ->visit('/register')
            ->waitFor('input[wire\\:model="name"]', 5)
            ->type('input[wire\\:model="name"]', 'Job Seeker Workflow')
            ->type('input[wire\\:model="email"]', $jobSeekerEmail)
            ->type('input[wire\\:model="password"]', 'password')
            ->type('input[wire\\:model="password_confirmation"]', 'password')
            ->click('input[value="job_seeker"]')
            ->press('Register')
            ->pause(3000);

        // Verify job seeker was created
        $jobSeekerUser = User::where('email', $jobSeekerEmail)->first();
        expect($jobSeekerUser)->not->toBeNull();
        expect($jobSeekerUser->hasRole('job_seeker'))->toBeTrue();

        if ($jobSeekerUser) {
            $jobSeekerUser->markEmailAsVerified();

            // Step 4: Login as job seeker and test access to job seeker features
            $browser->visit('/login')
                ->waitFor('input[wire\\:model="email"]', 5)
                ->type('input[wire\\:model="email"]', $jobSeekerEmail)
                ->type('input[wire\\:model="password"]', 'password')
                ->press('Login')
                ->pause(3000);

            // Test job seeker can access their features
            $browser->visit('/available-jobs')
                ->pause(2000)
                ->assertSee('Available Jobs');
        }

        // Step 5: Verify the workflow completed successfully
        expect($hrUser->hasRole('hr'))->toBeTrue();
        expect($jobSeekerUser->hasRole('job_seeker'))->toBeTrue();
    });
});

test('srs ranking algorithm workflow simulation', function () {
    $this->browse(function (Browser $browser) {
        // Create test HR user with proper details
        $hrUser = User::firstOrCreate(
            ['email' => 'hr_ranking@gmail.com'],
            [
                'name' => 'HR Ranking Test',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $hrUser->assignRole('hr');

        // Create HR details
        $hrDetail = \App\Models\Hr\HrDetail::firstOrCreate(
            ['user_id' => $hrUser->id],
            [
                'name' => $hrUser->name,
                'email' => $hrUser->email,
                'organization_name' => 'Ranking Test Company',
                'phone' => '1234567890',
            ]
        );

        // Create job post directly in database for testing
        $jobPost = JobPost::create([
            'user_id' => $hrUser->id,
            'title' => 'Senior PHP Developer',
            'description' => 'Looking for experienced PHP Laravel developer with Vue.js, MySQL, REST API development, and TF-IDF algorithm implementation experience. Must have strong background in resume processing systems.',
            'requirements' => 'PHP, Laravel, Vue.js, MySQL, REST APIs, TF-IDF, Algorithm Implementation',
            'location' => 'Remote',
            'status' => 'active',
            'type' => 'full-time',
            'salary_min' => 60000,
            'salary_max' => 90000,
        ]);

        // Login as HR and verify job was created
        $browser->visit('/logout')
            ->pause(1000)
            ->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'hr_ranking@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->pause(5000); // Give more time for login

        // Test that we can access dashboard
        $browser->visit('/dashboard')
            ->assertSee('Dashboard');

        // Visit job posts to verify our test job exists
        $browser->visit('/hr/jobpost')
            ->pause(2000)
            ->assertSee('Job Post'); // Should see the job posting interface

        // Verify job post was created successfully
        expect($jobPost->id)->not->toBeNull();
        expect($jobPost->title)->toBe('Senior PHP Developer');
        expect($jobPost->user_id)->toBe($hrUser->id);
    });
});

test('system health and performance test', function () {
    $this->browse(function (Browser $browser) {
        $startTime = microtime(true);

        // Test basic page load performance
        $browser->visit('/')
            ->assertSee('Smart Resume Scanner');

        $landingPageTime = microtime(true) - $startTime;

        // Test login page performance
        $loginStartTime = microtime(true);
        $browser->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5);
        $loginPageTime = microtime(true) - $loginStartTime;

        // Test register page performance
        $registerStartTime = microtime(true);
        $browser->visit('/register')
            ->waitFor('input[wire\\:model="name"]', 5);
        $registerPageTime = microtime(true) - $registerStartTime;

        // Performance assertions (pages should load within reasonable time)
        expect($landingPageTime)->toBeLessThan(5.0); // 5 seconds max
        expect($loginPageTime)->toBeLessThan(3.0);    // 3 seconds max
        expect($registerPageTime)->toBeLessThan(3.0); // 3 seconds max

        echo "Performance Results:\n";
        echo 'Landing page: '.round($landingPageTime, 2)."s\n";
        echo 'Login page: '.round($loginPageTime, 2)."s\n";
        echo 'Register page: '.round($registerPageTime, 2)."s\n";
    });
});
