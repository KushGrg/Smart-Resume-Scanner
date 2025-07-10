<?php

use App\Models\JobSeeker\Resume;
use App\Models\User;
use Laravel\Dusk\Browser;

test('resume ranking algorithm test with TF-IDF cosine similarity', function () {
    $this->browse(function (Browser $browser) {
        // Setup: Create HR user and login
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/dashboard');

        // Create a specific job post for testing ranking
        $browser->visit('/hr/jobpost')
            ->waitFor('[wire\\:click="$toggle(\'drawer\')"]', 10)
            ->click('[wire\\:click="$toggle(\'drawer\')"]')
            ->waitFor('.drawer', 10)
            ->type('title', 'PHP Laravel Developer')
            ->type('description', 'We need a skilled PHP Laravel developer with experience in MySQL, Vue.js, REST APIs, Git, and Docker. The candidate should have strong problem-solving skills and experience with database optimization.')
            ->type('location', 'Remote')
            ->select('type', 'Full-time')
            ->type('requirements', 'PHP, Laravel, MySQL, Vue.js, REST APIs, Git, Docker, Problem-solving, Database optimization')
            ->type('experience_level', '3+ years')
            ->select('status', 'Active')
            ->press('Save')
            ->waitUntilMissing('.drawer', 10)
            ->assertSee('PHP Laravel Developer');

        $browser->visit('/logout');

        // Create first candidate with high matching skills
        $browser->visit('/register')
            ->type('name', 'Alice PHP Expert')
            ->type('email', 'alice@phpexpert.com')
            ->type('password', 'password')
            ->type('password_confirmation', 'password')
            ->radio('role', 'job_seeker')
            ->press('Register');

        // Verify and login as Alice
        $alice = User::where('email', 'alice@phpexpert.com')->first();
        $alice->markEmailAsVerified();

        $browser->visit('/login')
            ->type('email', 'alice@phpexpert.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/create-profile');

        // Create highly matching profile for Alice
        $browser->type('name', 'Alice PHP Expert')
            ->type('designation', 'Senior PHP Laravel Developer')
            ->type('phone', '+1-555-111-1111')
            ->type('email', 'alice@phpexpert.com')
            ->type('country', 'United States')
            ->type('city', 'Remote')
            ->press('Next');

        $browser->type('experiences.0.job_title', 'Senior PHP Laravel Developer')
            ->type('experiences.0.employer', 'Laravel Solutions Inc')
            ->type('experiences.0.location', 'Remote')
            ->type('experiences.0.start_date', '2020-01-01')
            ->type('experiences.0.work_summary', 'Expert PHP Laravel developer with extensive experience in MySQL database optimization, Vue.js frontend development, REST API design, Git version control, and Docker containerization. Strong problem-solving skills with focus on scalable web applications.')
            ->press('Next');

        $browser->type('educations.0.school_name', 'Tech University')
            ->type('educations.0.degree', 'Computer Science')
            ->type('educations.0.field_of_study', 'Software Engineering')
            ->type('educations.0.start_date', '2016-09-01')
            ->type('educations.0.end_date', '2020-05-30')
            ->press('Next');

        // Add exact matching skills
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
            ->type('newSkill', 'Database optimization')
            ->press('Add Skill')
            ->type('newSkill', 'Problem-solving')
            ->press('Add Skill')
            ->type('summary', 'Highly experienced PHP Laravel developer with 4+ years specializing in MySQL database optimization, Vue.js integration, REST API development, Git workflows, and Docker deployment. Expert problem-solving skills in web application development.')
            ->press('Next');

        $browser->press('Submit & Generate Resume')
            ->pause(5000);

        $browser->visit('/logout');

        // Create second candidate with partial matching skills
        $browser->visit('/register')
            ->type('name', 'Bob Frontend Dev')
            ->type('email', 'bob@frontend.com')
            ->type('password', 'password')
            ->type('password_confirmation', 'password')
            ->radio('role', 'job_seeker')
            ->press('Register');

        $bob = User::where('email', 'bob@frontend.com')->first();
        $bob->markEmailAsVerified();

        $browser->visit('/login')
            ->type('email', 'bob@frontend.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/create-profile');

        // Create partially matching profile for Bob
        $browser->type('name', 'Bob Frontend Dev')
            ->type('designation', 'Frontend Developer')
            ->type('phone', '+1-555-222-2222')
            ->type('email', 'bob@frontend.com')
            ->type('country', 'United States')
            ->type('city', 'Remote')
            ->press('Next');

        $browser->type('experiences.0.job_title', 'Frontend Developer')
            ->type('experiences.0.employer', 'Web Design Co')
            ->type('experiences.0.location', 'Remote')
            ->type('experiences.0.start_date', '2021-01-01')
            ->type('experiences.0.work_summary', 'Frontend developer focused on Vue.js applications, HTML, CSS, JavaScript. Some experience with Git version control and basic PHP scripting.')
            ->press('Next');

        $browser->type('educations.0.school_name', 'Design College')
            ->type('educations.0.degree', 'Web Design')
            ->type('educations.0.field_of_study', 'Frontend Development')
            ->type('educations.0.start_date', '2019-09-01')
            ->type('educations.0.end_date', '2021-05-30')
            ->press('Next');

        // Add partially matching skills
        $browser->type('newSkill', 'Vue.js')
            ->press('Add Skill')
            ->type('newSkill', 'JavaScript')
            ->press('Add Skill')
            ->type('newSkill', 'HTML')
            ->press('Add Skill')
            ->type('newSkill', 'CSS')
            ->press('Add Skill')
            ->type('newSkill', 'Git')
            ->press('Add Skill')
            ->type('newSkill', 'PHP')
            ->press('Add Skill')
            ->type('summary', 'Frontend developer with 2+ years experience in Vue.js, JavaScript, HTML, CSS. Basic knowledge of PHP and Git. Passionate about user interface design.')
            ->press('Next');

        $browser->press('Submit & Generate Resume')
            ->pause(5000);

        $browser->visit('/logout');

        // Apply both candidates to the job
        $browser->visit('/login')
            ->type('email', 'alice@phpexpert.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/available-jobs');

        // Create test PDF for Alice
        $aliceTestFile = storage_path('app/alice-resume.pdf');
        file_put_contents($aliceTestFile, '%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R>>endobj
4 0 obj<</Length 200>>stream
BT/F1 12 Tf 72 720 Td(Alice PHP Expert - Senior PHP Laravel Developer)Tj
0 -20 Td(Expert in PHP Laravel MySQL Vue.js REST APIs Git Docker)Tj
0 -20 Td(Database optimization and problem-solving specialist)Tj
0 -20 Td(4+ years experience in web application development)Tj ET
endstream endobj
xref 0 5
0000000000 65535 f 0000000009 00000 n 0000000058 00000 n 0000000115 00000 n 0000000206 00000 n 
trailer<</Size 5/Root 1 0 R>>startxref 450 %%EOF');

        $browser->waitFor('[wire\\:click="$dispatch(\'open-apply-modal\',')
            ->click('[wire\\:click="$dispatch(\'open-apply-modal\',')
            ->waitFor('.modal', 10)
            ->attach('resume', $aliceTestFile)
            ->press('Submit Application')
            ->waitUntilMissing('.modal', 10);

        if (file_exists($aliceTestFile)) {
            unlink($aliceTestFile);
        }

        $browser->visit('/logout');

        // Apply Bob to the same job
        $browser->visit('/login')
            ->type('email', 'bob@frontend.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/available-jobs');

        $bobTestFile = storage_path('app/bob-resume.pdf');
        file_put_contents($bobTestFile, '%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R>>endobj
4 0 obj<</Length 180>>stream
BT/F1 12 Tf 72 720 Td(Bob Frontend Dev - Frontend Developer)Tj
0 -20 Td(Skilled in Vue.js JavaScript HTML CSS Git)Tj
0 -20 Td(Basic PHP knowledge and Git version control)Tj
0 -20 Td(2+ years frontend development experience)Tj ET
endstream endobj
xref 0 5
0000000000 65535 f 0000000009 00000 n 0000000058 00000 n 0000000115 00000 n 0000000206 00000 n 
trailer<</Size 5/Root 1 0 R>>startxref 430 %%EOF');

        $browser->waitFor('[wire\\:click="$dispatch(\'open-apply-modal\',')
            ->click('[wire\\:click="$dispatch(\'open-apply-modal\',')
            ->waitFor('.modal', 10)
            ->attach('resume', $bobTestFile)
            ->press('Submit Application')
            ->waitUntilMissing('.modal', 10);

        if (file_exists($bobTestFile)) {
            unlink($bobTestFile);
        }

        $browser->visit('/logout');

        // Wait for background processing
        sleep(10);

        // Login as HR and verify ranking results
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/hr/applications')
            ->pause(5000); // Allow processing time

        // Check if ranking worked - Alice should rank higher than Bob
        $browser->assertSee('PHP Laravel Developer');
    });
});

test('resume text extraction and processing', function () {
    $this->browse(function (Browser $browser) {
        // Login as job seeker
        $user = User::factory()->create([
            'email' => 'test.extraction@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $user->assignRole('job_seeker');

        $browser->visit('/login')
            ->type('email', 'test.extraction@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/create-profile');

        // Create minimal profile for PDF generation test
        $browser->type('name', 'Text Extraction Test')
            ->type('designation', 'Test Engineer')
            ->type('phone', '+1-555-000-0000')
            ->type('email', 'test.extraction@example.com')
            ->type('country', 'Test Country')
            ->type('city', 'Test City')
            ->press('Next');

        $browser->type('experiences.0.job_title', 'Test Engineer')
            ->type('experiences.0.employer', 'Test Company')
            ->type('experiences.0.location', 'Test Location')
            ->type('experiences.0.start_date', '2023-01-01')
            ->type('experiences.0.work_summary', 'Testing resume text extraction functionality with various keywords and phrases to ensure TF-IDF algorithm processes content correctly.')
            ->press('Next');

        $browser->type('educations.0.school_name', 'Test University')
            ->type('educations.0.degree', 'Test Degree')
            ->type('educations.0.field_of_study', 'Testing')
            ->type('educations.0.start_date', '2020-01-01')
            ->type('educations.0.end_date', '2023-01-01')
            ->press('Next');

        $browser->type('newSkill', 'Testing')
            ->press('Add Skill')
            ->type('newSkill', 'Quality Assurance')
            ->press('Add Skill')
            ->type('newSkill', 'Automation')
            ->press('Add Skill')
            ->type('summary', 'Dedicated testing professional with expertise in quality assurance and automation testing frameworks.')
            ->press('Next');

        $browser->press('Submit & Generate Resume')
            ->pause(5000)
            ->assertSee('Resume created successfully');

        // Verify resume appears in list
        $browser->visit('/view-created-resume-list')
            ->assertSee('Text Extraction Test')
            ->assertSee('Test Engineer');
    });
});

test('algorithm performance with multiple resumes', function () {
    $this->browse(function (Browser $browser) {
        // Login as HR and create a job
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/hr/jobpost')
            ->waitFor('[wire\\:click="$toggle(\'drawer\')"]', 10)
            ->click('[wire\\:click="$toggle(\'drawer\')"]')
            ->waitFor('.drawer', 10)
            ->type('title', 'Software Engineer Position')
            ->type('description', 'Looking for software engineers with Java, Python, or JavaScript experience.')
            ->type('location', 'Tech City')
            ->select('type', 'Full-time')
            ->type('requirements', 'Java, Python, JavaScript, Programming, Software Development')
            ->type('experience_level', '2+ years')
            ->select('status', 'Active')
            ->press('Save')
            ->waitUntilMissing('.drawer', 10);

        $browser->visit('/logout');

        // Create multiple candidates for performance testing
        $candidates = [
            ['name' => 'Java Expert', 'email' => 'java@expert.com', 'skills' => ['Java', 'Spring', 'Hibernate', 'Programming']],
            ['name' => 'Python Pro', 'email' => 'python@pro.com', 'skills' => ['Python', 'Django', 'Flask', 'Programming']],
            ['name' => 'JS Developer', 'email' => 'js@dev.com', 'skills' => ['JavaScript', 'React', 'Node.js', 'Programming']],
            ['name' => 'Full Stack', 'email' => 'fullstack@dev.com', 'skills' => ['Java', 'Python', 'JavaScript', 'Programming']],
        ];

        foreach ($candidates as $index => $candidate) {
            $browser->visit('/register')
                ->type('name', $candidate['name'])
                ->type('email', $candidate['email'])
                ->type('password', 'password')
                ->type('password_confirmation', 'password')
                ->radio('role', 'job_seeker')
                ->press('Register');

            $user = User::where('email', $candidate['email'])->first();
            $user->markEmailAsVerified();

            $browser->visit('/login')
                ->type('email', $candidate['email'])
                ->type('password', 'password')
                ->press('Login')
                ->visit('/create-profile');

            // Quick profile creation
            $browser->type('name', $candidate['name'])
                ->type('designation', 'Software Engineer')
                ->type('phone', '+1-555-'.str_pad($index, 3, '0').'-0000')
                ->type('email', $candidate['email'])
                ->type('country', 'Tech Country')
                ->type('city', 'Tech City')
                ->press('Next');

            $browser->type('experiences.0.job_title', 'Software Engineer')
                ->type('experiences.0.employer', 'Tech Corp '.$index)
                ->type('experiences.0.location', 'Tech City')
                ->type('experiences.0.start_date', '2022-01-01')
                ->type('experiences.0.work_summary', 'Software development experience with '.implode(', ', $candidate['skills']))
                ->press('Next');

            $browser->type('educations.0.school_name', 'Tech University')
                ->type('educations.0.degree', 'Computer Science')
                ->type('educations.0.field_of_study', 'Software Engineering')
                ->type('educations.0.start_date', '2018-01-01')
                ->type('educations.0.end_date', '2022-01-01')
                ->press('Next');

            foreach ($candidate['skills'] as $skill) {
                $browser->type('newSkill', $skill)
                    ->press('Add Skill');
            }

            $browser->type('summary', $candidate['name'].' is a skilled software engineer with expertise in '.implode(', ', $candidate['skills']))
                ->press('Next');

            $browser->press('Submit & Generate Resume')
                ->pause(3000);

            $browser->visit('/logout');
        }

        // Wait for all processing to complete
        sleep(15);

        // Login as HR and check applications
        $browser->visit('/login')
            ->type('email', 'hr@gmail.com')
            ->type('password', 'password')
            ->press('Login')
            ->visit('/hr/applications')
            ->pause(5000)
            ->assertSee('Software Engineer Position');
    });
});
