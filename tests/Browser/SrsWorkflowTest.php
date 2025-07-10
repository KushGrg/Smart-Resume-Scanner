<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('complete SRS workflow - HR job posting, job seeker registration, and application', function () {
    // Step 1: HR Login and Job Posting
    $this->browse(function (Browser $browser) {
        // Login as HR
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');

        // Navigate to Job Post and create a new job
        $browser->visit('/hr/jobpost')
            ->assertSee('Job Posts Management')
            ->waitFor('[wire\\:click="$toggle(\'drawer\')"]', 10)
            ->click('[wire\\:click="$toggle(\'drawer\')"]')
            ->waitFor('.drawer', 10)
            ->type('title', 'Senior Laravel Developer')
            ->type('description', 'We are looking for an experienced Laravel developer with expertise in PHP, MySQL, Vue.js, and REST APIs. The ideal candidate should have 3+ years of experience building scalable web applications.')
            ->type('location', 'New York, USA')
            ->select('type', 'Full-time')
            ->type('requirements', 'PHP, Laravel, MySQL, Vue.js, REST APIs, Git, Docker')
            ->type('experience_level', '3+ years')
            ->select('status', 'Active')
            ->press('Save')
            ->waitUntilMissing('.drawer', 10)
            ->assertSee('Senior Laravel Developer');

        $browser->visit('/logout');
    });

    // Step 2: Job Seeker Registration and Profile Creation
    $this->browse(function (Browser $browser) {
        // Register as Job Seeker
        $browser->visit('/register')
            ->type('name', 'John Developer')
            ->type('email', 'john@developer.com')
            ->type('password', 'password')
            ->type('password_confirmation', 'password')
            ->radio('role', 'job_seeker')
            ->press('Register')
            ->assertSee('Verify Your Email');

        // Manually verify the user (simulate email verification)
        $user = User::where('email', 'john@developer.com')->first();
        $user->markEmailAsVerified();

        // Login as the verified job seeker
        $browser->visit('/login')
            ->type('email', 'john@developer.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Navigate to Create Profile and complete the 5-step wizard
        $browser->visit('/create-profile')
            ->assertSee('Step 1 of 5');

        // Step 1: Profile Information
        $browser->type('name', 'John Developer')
            ->type('designation', 'Senior Laravel Developer')
            ->type('phone', '+1-555-123-4567')
            ->type('email', 'john@developer.com')
            ->type('country', 'United States')
            ->type('city', 'New York')
            ->type('address', '123 Tech Street, New York, NY 10001')
            ->press('Next')
            ->assertSee('Step 2 of 5');

        // Step 2: Experience
        $browser->type('experiences.0.job_title', 'Laravel Developer')
            ->type('experiences.0.employer', 'TechCorp Inc')
            ->type('experiences.0.location', 'New York, NY')
            ->type('experiences.0.start_date', '2020-01-15')
            ->type('experiences.0.end_date', '2024-12-31')
            ->type('experiences.0.work_summary', 'Developed and maintained multiple Laravel applications. Built REST APIs, implemented user authentication, worked with MySQL databases, and integrated Vue.js frontend components.')
            ->press('Next')
            ->assertSee('Step 3 of 5');

        // Step 3: Education
        $browser->type('educations.0.school_name', 'New York University')
            ->type('educations.0.location', 'New York, NY')
            ->type('educations.0.degree', 'Bachelor of Science')
            ->type('educations.0.field_of_study', 'Computer Science')
            ->type('educations.0.start_date', '2016-09-01')
            ->type('educations.0.end_date', '2020-05-30')
            ->type('educations.0.description', 'Focused on software engineering, database systems, and web development technologies.')
            ->press('Next')
            ->assertSee('Step 4 of 5');

        // Step 4: Skills & Summary
        $browser->type('newSkill', 'PHP')
            ->press('Add Skill')
            ->type('newSkill', 'Laravel')
            ->press('Add Skill')
            ->type('newSkill', 'MySQL')
            ->press('Add Skill')
            ->type('newSkill', 'Vue.js')
            ->press('Add Skill')
            ->type('newSkill', 'REST APIs')
            ->press('Add Skill')
            ->type('newSkill', 'Git')
            ->press('Add Skill')
            ->type('newSkill', 'Docker')
            ->press('Add Skill')
            ->type('summary', 'Experienced Laravel developer with 4+ years of building scalable web applications. Proficient in PHP, MySQL, Vue.js, and modern development practices. Strong background in REST API development, database optimization, and frontend integration.')
            ->press('Next')
            ->assertSee('Step 5 of 5');

        // Step 5: Review and Submit
        $browser->assertSee('John Developer')
            ->assertSee('Senior Laravel Developer')
            ->assertSee('PHP')
            ->assertSee('Laravel')
            ->assertSee('MySQL')
            ->press('Submit & Generate Resume')
            ->pause(5000) // Wait for PDF generation
            ->assertSee('Resume created successfully');
    });

    // Step 3: Job Application Process
    $this->browse(function (Browser $browser) {
        // Navigate to available jobs and apply
        $browser->visit('/available-jobs')
            ->assertSee('Senior Laravel Developer')
            ->assertSee('New York, USA');

        // Apply for the job
        $browser->waitFor('[wire\\:click="$dispatch(\'open-apply-modal\',')
            ->click('[wire\\:click="$dispatch(\'open-apply-modal\',')
            ->waitFor('.modal', 10)
            ->assertSee('Apply for: Senior Laravel Developer');

        // Create a temporary test file for upload (PDF simulation)
        $testFile = storage_path('app/test-resume.pdf');
        file_put_contents($testFile, '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj
2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj
3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
>>
endobj
4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
72 720 Td
(Test PDF Resume Content) Tj
ET
endstream
endobj
xref
0 5
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000206 00000 n 
trailer
<<
/Size 5
/Root 1 0 R
>>
startxref
300
%%EOF');

        $browser->attach('resume', $testFile)
            ->press('Submit Application')
            ->waitUntilMissing('.modal', 10)
            ->assertSee('Application submitted successfully');

        // Clean up test file
        if (file_exists($testFile)) {
            unlink($testFile);
        }

        $browser->visit('/logout');
    });

    // Step 4: HR Review of Applications
    $this->browse(function (Browser $browser) {
        // Login back as HR
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Navigate to View Applications
        $browser->visit('/hr/applications')
            ->assertSee('Job Applications')
            ->assertSee('Review and manage candidate applications');

        // Wait for the page to load and check for applications
        $browser->pause(3000);

        // Test filtering functionality
        $browser->select('[wire\\:model\\.live="statusFilter"]', 'pending')
            ->type('[wire\\:model\\.live="search"]', 'John')
            ->pause(2000); // Wait for live search

        $browser->visit('/logout');
    });

    // Step 5: Verify Resume Creation and Listing
    $this->browse(function (Browser $browser) {
        // Login back as Job Seeker
        $browser->visit('/login')
            ->type('email', 'john@developer.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Check created resume list
        $browser->visit('/view-created-resume-list')
            ->assertSee('John Developer')
            ->assertSee('Senior Laravel Developer');

        // Check applied jobs history
        $browser->visit('/view-applied-history')
            ->assertSee('Your Job Applications')
            ->pause(2000); // Allow for any async loading

        $browser->visit('/logout');
    });
});

test('HR job posting workflow', function () {
    $this->browse(function (Browser $browser) {
        // Login as HR
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Test job creation
        $browser->visit('/hr/jobpost')
            ->waitFor('[wire\\:click="$toggle(\'drawer\')"]', 10)
            ->click('[wire\\:click="$toggle(\'drawer\')"]')
            ->waitFor('.drawer', 10)
            ->type('title', 'Full Stack Developer')
            ->type('description', 'Looking for a full stack developer with React and Node.js experience.')
            ->type('location', 'San Francisco, CA')
            ->select('type', 'Full-time')
            ->type('requirements', 'React, Node.js, JavaScript, MongoDB')
            ->type('experience_level', '2+ years')
            ->select('status', 'Active')
            ->press('Save')
            ->waitUntilMissing('.drawer', 10)
            ->assertSee('Full Stack Developer');

        // Test search functionality
        $browser->type('input[placeholder*="Search"]', 'Full Stack')
            ->pause(2000)
            ->assertSee('Full Stack Developer');
    });
});

test('job seeker resume creation workflow', function () {
    $this->browse(function (Browser $browser) {
        // Create and login as job seeker
        $user = User::factory()->create([
            'email' => 'test.seeker@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('job_seeker');

        $browser->visit('/login')
            ->type('email', 'test.seeker@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Test profile creation wizard
        $browser->visit('/create-profile')
            ->assertSee('Step 1 of 5')
            ->type('name', 'Test User')
            ->type('designation', 'Software Engineer')
            ->type('phone', '+1-555-987-6543')
            ->type('email', 'test.seeker@example.com')
            ->type('country', 'Canada')
            ->type('city', 'Toronto')
            ->press('Next')
            ->assertSee('Step 2 of 5');

        // Add experience
        $browser->type('experiences.0.job_title', 'Software Engineer')
            ->type('experiences.0.employer', 'Tech Solutions Ltd')
            ->type('experiences.0.location', 'Toronto, ON')
            ->type('experiences.0.start_date', '2022-01-01')
            ->type('experiences.0.work_summary', 'Developed web applications using modern frameworks.')
            ->press('Next')
            ->assertSee('Step 3 of 5');

        // Add education
        $browser->type('educations.0.school_name', 'University of Toronto')
            ->type('educations.0.degree', 'Bachelor of Computer Science')
            ->type('educations.0.field_of_study', 'Computer Science')
            ->type('educations.0.start_date', '2018-09-01')
            ->type('educations.0.end_date', '2022-05-30')
            ->press('Next')
            ->assertSee('Step 4 of 5');

        // Add skills
        $browser->type('newSkill', 'JavaScript')
            ->press('Add Skill')
            ->type('newSkill', 'React')
            ->press('Add Skill')
            ->type('summary', 'Passionate software engineer with experience in modern web technologies.')
            ->press('Next')
            ->assertSee('Step 5 of 5');

        // Submit
        $browser->press('Submit & Generate Resume')
            ->pause(5000)
            ->assertSee('Resume created successfully');

        // Verify resume in list
        $browser->visit('/view-created-resume-list')
            ->assertSee('Test User')
            ->assertSee('Software Engineer');
    });
});

test('authentication and navigation', function () {
    $this->browse(function (Browser $browser) {
        // Test login page
        $browser->visit('/login')
            ->assertSee('Email')
            ->assertSee('Password')
            ->assertSee('Login');

        // Test invalid login
        $browser->type('email', 'invalid@example.com')
            ->type('password', 'wrongpassword')
            ->press('Login')
            ->assertSee('These credentials do not match our records');

        // Test valid HR login
        $browser->clear('email')
            ->clear('password')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard');

        // Test navigation menu
        $browser->assertSee('Job Posts')
            ->assertSee('Applications')
            ->assertSee('Logout');

        // Test logout
        $browser->visit('/logout')
            ->assertPathIs('/');
    });
});

test('responsive design and UI elements', function () {
    $this->browse(function (Browser $browser) {
        // Test different viewport sizes
        $browser->resize(1200, 800)
            ->visit('/login')
            ->assertSee('Login');

        // Test mobile viewport
        $browser->resize(375, 667)
            ->refresh()
            ->assertSee('Login');

        // Test tablet viewport
        $browser->resize(768, 1024)
            ->refresh()
            ->assertSee('Login');

        // Back to desktop
        $browser->resize(1920, 1080)
            ->refresh()
            ->assertSee('Login');
    });
});
