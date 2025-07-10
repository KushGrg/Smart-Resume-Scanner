<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('can access landing page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Smart Resume Scanner')
            ->assertSee('Login')
            ->assertSee('Register');
    });
});

test('can access login page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->assertSee('Login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->assertPresent('input[wire\\:model="password"]')
            ->assertPresent('button[type="submit"]');
    });
});

test('can access register page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
            ->assertSee('Register')
            ->waitFor('input[wire\\:model="name"]', 5)
            ->assertPresent('input[wire\\:model="email"]')
            ->assertPresent('input[wire\\:model="password"]')
            ->assertPresent('input[wire\\:model="password_confirmation"]')
            ->assertSee('Select Role');
    });
});

test('hr user can login and access dashboard', function () {
    // Create HR user if not exists
    $hrUser = User::firstOrCreate(
        ['email' => 'hr@gmail.com'],
        [
            'name' => 'HR User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );
    $hrUser->assignRole('hr');

    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'hr@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->waitForText('Dashboard', 10)
            ->assertSee('Dashboard');
    });
});

test('admin user can login and access admin panel', function () {
    // Create admin user if not exists
    $adminUser = User::firstOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );
    $adminUser->assignRole('admin');

    $this->browse(function (Browser $browser) {
        // Ensure we start from logged out state
        $browser->visit('/logout')
            ->pause(1000)
            ->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'admin@example.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->waitForText('Dashboard', 10)
            ->assertSee('Dashboard');
    });
});

test('job seeker can register successfully', function () {
    $this->browse(function (Browser $browser) {
        // Ensure we start from logged out state
        $browser->visit('/logout')
            ->pause(1000);

        $email = 'testjobseeker'.time().'@example.com';

        $browser->visit('/register')
            ->waitFor('input[wire\\:model="name"]', 5)
            ->type('input[wire\\:model="name"]', 'Test Job Seeker')
            ->type('input[wire\\:model="email"]', $email)
            ->type('input[wire\\:model="password"]', 'password')
            ->type('input[wire\\:model="password_confirmation"]', 'password')
            ->click('input[value="job_seeker"]') // Click radio button for job_seeker role
            ->press('Register')
            ->pause(3000); // Wait for registration to complete

        // Registration should succeed - check that user was created
        $user = User::where('email', $email)->first();
        expect($user)->not->toBeNull();
        expect($user->hasRole('job_seeker'))->toBeTrue();

        // Verify user can be found in database
        $browser->assertDontSee('error'); // Make sure no errors occurred
    });
});

test('authenticated user can access dashboard', function () {
    $hrUser = User::firstOrCreate(
        ['email' => 'hr@gmail.com'],
        [
            'name' => 'HR User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );
    $hrUser->assignRole('hr');

    $this->browse(function (Browser $browser) {
        // Ensure we start from logged out state
        $browser->visit('/logout')
            ->pause(1000);

        // Login first
        $browser->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'hr@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->waitForText('Dashboard', 10);

        // Test that we can access the dashboard
        $browser->visit('/dashboard')
            ->assertSee('Dashboard')
            ->pause(1000);

        // Test logout functionality
        $browser->visit('/logout')
            ->pause(2000)
            ->assertPathIs('/');
    });
});

test('responsive design works on mobile viewport', function () {
    $this->browse(function (Browser $browser) {
        // Ensure we start from logged out state
        $browser->visit('/logout')
            ->pause(1000);

        $browser->resize(375, 667) // iPhone size
            ->visit('/')
            ->assertSee('Smart Resume Scanner')
            ->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->assertPresent('input[wire\\:model="password"]')
            ->resize(1920, 1080); // Reset to desktop size
    });
});

test('database connection is working', function () {
    $this->browse(function (Browser $browser) {
        // Test that we can query the database
        $userCount = User::count();
        expect($userCount)->toBeGreaterThanOrEqual(0);

        $browser->visit('/')
            ->assertSee('Smart Resume Scanner');
    });
});
