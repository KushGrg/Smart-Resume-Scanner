<?php

use Laravel\Dusk\Browser;

test('simple login test', function () {
    $this->browse(function (Browser $browser) {
        // Test HR login
        $browser->visit('/login')
            ->waitFor('input[wire\\:model="email"]', 5)
            ->type('input[wire\\:model="email"]', 'hr@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->pause(3000); // Wait for redirection

        // Check if we're redirected somewhere
        $currentUrl = $browser->driver->getCurrentURL();
        echo 'After login URL: '.$currentUrl."\n";

        // Try to visit dashboard directly
        $browser->visit('/dashboard')
            ->pause(2000);

        $dashboardUrl = $browser->driver->getCurrentURL();
        echo 'Dashboard URL: '.$dashboardUrl."\n";
    });
});
