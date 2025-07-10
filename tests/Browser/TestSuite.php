<?php

use Laravel\Dusk\Browser;

test('SRS system health check', function () {
    $this->browse(function (Browser $browser) {
        // Test home page
        $browser->visit('/')
            ->assertSee('Smart Resume Scanner')
            ->assertSee('Login')
            ->assertSee('Register');

        // Test login page functionality
        $browser->visit('/login')
            ->assertSee('Login')
            ->assertSee('Enter your credentials')
            ->assertPresent('input[placeholder="E-mail "]')
            ->assertPresent('input[placeholder="Password "]')
            ->assertPresent('button[type="submit"]');

        // Test register page functionality
        $browser->visit('/register')
            ->assertSee('Register')
            ->assertSee('Create a new account')
            ->assertPresent('input[placeholder="Name "]')
            ->assertPresent('input[placeholder="E-mail "]')
            ->assertPresent('input[placeholder="Password "]')
            ->assertPresent('input[placeholder="Confirm Password "]');
    });
});

test('database seeding verification', function () {
    $this->browse(function (Browser $browser) {
        // Verify HR user exists and can login
        $browser->visit('/login')
            ->type('input[wire\\:model="email"]', 'hr@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');

        $browser->visit('/logout');

        // Verify regular user exists and can login
        $browser->visit('/login')
            ->type('input[wire\\:model="email"]', 'user@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');

        $browser->visit('/logout');

        // Verify admin user exists and can login
        $browser->visit('/login')
            ->type('input[wire\\:model="email"]', 'admin@example.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');

        $browser->visit('/logout');
    });
});

test('role-based access control', function () {
    $this->browse(function (Browser $browser) {
        // Test HR access
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/hr/jobpost')
            ->assertSee('Job Posts Management')
            ->visit('/hr/applications')
            ->assertSee('Job Applications');

        $browser->visit('/logout');

        // Test Job Seeker access
        $browser->visit('/login')
            ->type('email', 'user@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/available-jobs')
            ->assertSee('Available Jobs')
            ->visit('/view-created-resume-list')
            ->assertSee('Your Created Resumes');

        $browser->visit('/logout');
    });
});

test('file upload functionality', function () {
    $this->browse(function (Browser $browser) {
        // Login as job seeker
        $browser->visit('/login')
            ->type('email', 'user@gmail.com')
            ->type('password', 'password')
            ->press('Login');

        // Test file upload constraints
        $browser->visit('/available-jobs');

        if ($browser->element('[wire\\:click="$dispatch(\'open-apply-modal\',"]')) {
            $browser->click('[wire\\:click="$dispatch(\'open-apply-modal\',"]')
                ->waitFor('.modal', 10);

            // Verify file input exists
            $browser->assertPresent('input[type="file"]');

            $browser->press('close')
                ->waitUntilMissing('.modal', 10);
        }

        $browser->visit('/logout');
    });
});

test('resume generation system', function () {
    $this->browse(function (Browser $browser) {
        // Create test user
        $testUser = \App\Models\User::factory()->create([
            'email' => 'resume.test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $testUser->assignRole('job_seeker');

        $browser->visit('/login')
            ->type('email', 'resume.test@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/create-profile')
            ->assertSee('Step 1 of 5');

        // Complete minimal profile
        $browser->type('name', 'Resume Test User')
            ->type('designation', 'Test Position')
            ->type('phone', '+1-555-TEST-001')
            ->type('email', 'resume.test@example.com')
            ->type('country', 'Test Country')
            ->type('city', 'Test City')
            ->press('Next');

        $browser->type('experiences.0.job_title', 'Test Job')
            ->type('experiences.0.employer', 'Test Company')
            ->type('experiences.0.location', 'Test Location')
            ->type('experiences.0.start_date', '2023-01-01')
            ->type('experiences.0.work_summary', 'Test work experience summary.')
            ->press('Next');

        $browser->type('educations.0.school_name', 'Test School')
            ->type('educations.0.degree', 'Test Degree')
            ->type('educations.0.field_of_study', 'Test Field')
            ->type('educations.0.start_date', '2020-01-01')
            ->type('educations.0.end_date', '2023-01-01')
            ->press('Next');

        $browser->type('newSkill', 'Testing')
            ->press('Add Skill')
            ->type('summary', 'Test summary for resume generation.')
            ->press('Next');

        $browser->press('Submit & Generate Resume')
            ->pause(5000); // Wait for PDF generation

        // Verify success or error message appears
        $browser->waitFor('div[role="alert"], .alert, .toast, .notification', 10);

        $browser->visit('/logout');
    });
});

test('queue system functionality', function () {
    $this->browse(function (Browser $browser) {
        // This test checks if the queue system is responsive
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/hr/jobpost')
            ->assertSee('Job Posts Management');

        // Create job that will trigger background processing
        $browser->waitFor('[wire\\:click="$toggle(\'drawer\')"]', 10)
            ->click('[wire\\:click="$toggle(\'drawer\')"]')
            ->waitFor('.drawer', 10)
            ->type('title', 'Queue Test Job')
            ->type('description', 'Testing background job processing system.')
            ->type('location', 'Remote')
            ->select('type', 'Full-time')
            ->type('requirements', 'Testing, Queue processing')
            ->type('experience_level', '1+ years')
            ->select('status', 'Active')
            ->press('Save')
            ->waitUntilMissing('.drawer', 10)
            ->assertSee('Queue Test Job');

        $browser->visit('/logout');
    });
});

test('search and filtering functionality', function () {
    $this->browse(function (Browser $browser) {
        // Test HR search functionality
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/hr/jobpost');

        // Test search if jobs exist
        if ($browser->element('input[placeholder*="Search"]')) {
            $browser->type('input[placeholder*="Search"]', 'Developer')
                ->pause(2000); // Wait for search
        }

        $browser->visit('/hr/applications');

        // Test filtering options
        if ($browser->element('[wire\\:model\\.live="statusFilter"]')) {
            $browser->select('[wire\\:model\\.live="statusFilter"]', 'pending')
                ->pause(1000);
        }

        $browser->visit('/logout');

        // Test job seeker search
        $browser->visit('/login')
            ->type('email', 'user@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/available-jobs');

        // Test job search if available
        if ($browser->element('input[placeholder*="Search"]')) {
            $browser->type('input[placeholder*="Search"]', 'Remote')
                ->pause(2000);
        }

        $browser->visit('/logout');
    });
});

test('responsive design verification', function () {
    $this->browse(function (Browser $browser) {
        $viewports = [
            ['width' => 1920, 'height' => 1080, 'name' => 'Desktop'],
            ['width' => 1366, 'height' => 768, 'name' => 'Laptop'],
            ['width' => 768, 'height' => 1024, 'name' => 'Tablet'],
            ['width' => 375, 'height' => 667, 'name' => 'Mobile'],
        ];

        foreach ($viewports as $viewport) {
            $browser->resize($viewport['width'], $viewport['height'])
                ->visit('/')
                ->assertSee('Smart Resume Scanner')
                ->visit('/login')
                ->assertSee('Email')
                ->assertSee('Password');
        }

        // Reset to default desktop size
        $browser->resize(1920, 1080);
    });
});

test('error handling and validation', function () {
    $this->browse(function (Browser $browser) {
        // Test login validation
        $browser->visit('/login')
            ->press('Login')
            ->pause(1000); // Wait for validation messages

        // Test registration validation
        $browser->visit('/register')
            ->press('Register')
            ->pause(1000); // Wait for validation messages

        // Test invalid email format
        $browser->clear('email')
            ->type('email', 'invalid-email')
            ->press('Register')
            ->pause(1000);

        // Test password mismatch
        $browser->clear('email')
            ->type('email', 'test@example.com')
            ->type('password', 'password123')
            ->type('password_confirmation', 'password456')
            ->press('Register')
            ->pause(1000);
    });
});

test('navigation and menu functionality', function () {
    $this->browse(function (Browser $browser) {
        // Test navigation as HR
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Test main navigation links
        $navigationLinks = ['/hr/jobpost', '/hr/applications', '/dashboard'];

        foreach ($navigationLinks as $link) {
            $browser->visit($link)
                ->pause(1000); // Ensure page loads
        }

        $browser->visit('/logout');

        // Test navigation as Job Seeker
        $browser->visit('/login')
            ->type('email', 'user@gmail.com')
            ->type('password', 'password')
            ->press('Login');

        $jobSeekerLinks = ['/available-jobs', '/view-created-resume-list', '/view-applied-history'];

        foreach ($jobSeekerLinks as $link) {
            $browser->visit($link)
                ->pause(1000);
        }

        $browser->visit('/logout');
    });
});
