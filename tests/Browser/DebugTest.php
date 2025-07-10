<?php

use App\Models\User;
use Laravel\Dusk\Browser;

test('debug job seeker registration', function () {
    $this->browse(function (Browser $browser) {
        // Ensure we start from logged out state
        $browser->visit('/logout')
            ->pause(1000);

        $email = 'debug'.time().'@example.com';

        $browser->visit('/register')
            ->waitFor('input[wire\\:model="name"]', 5)
            ->type('input[wire\\:model="name"]', 'Debug User')
            ->type('input[wire\\:model="email"]', $email)
            ->type('input[wire\\:model="password"]', 'password')
            ->type('input[wire\\:model="password_confirmation"]', 'password')
            ->click('input[value="job_seeker"]')
            ->press('Register')
            ->pause(3000);

        // Take screenshot and get page source for debugging
        $browser->screenshot('debug-after-registration');

        // Get current URL and page text
        $currentUrl = $browser->driver->getCurrentURL();
        $pageText = $browser->text('body');

        echo 'Current URL: '.$currentUrl."\n";
        echo 'Page text contains: '.substr($pageText, 0, 500)."...\n";
    });
});

test('debug navigation structure', function () {
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
        $browser->visit('/logout')
            ->pause(1000)
            ->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'hr@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->waitForText('Dashboard', 10);

        // Take screenshot
        $browser->screenshot('debug-dashboard');

        // Get page HTML for debugging navigation structure
        $html = $browser->driver->getPageSource();

        // Look for menu-related classes
        if (preg_match_all('/class="[^"]*menu[^"]*"/', $html, $matches)) {
            echo 'Found menu classes: '.implode(', ', $matches[0])."\n";
        } else {
            echo "No menu classes found\n";
        }

        // Check if sidebar is present
        if (strpos($html, 'sidebar') !== false) {
            echo "Sidebar found in HTML\n";
        } else {
            echo "No sidebar found\n";
        }
    });
});
