<?php

use Laravel\Dusk\Browser;

test('debug login process', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('input[wire\\:model="email"]', 'hr@gmail.com')
            ->type('input[wire\\:model="password"]', 'password')
            ->press('Login')
            ->pause(2000) // Wait for form submission
            ->screenshot('after-login-attempt');

        // Check what's on the page
        if ($browser->element('body')) {
            echo 'Current URL: '.$browser->url()."\n";
            echo 'Page content contains Dashboard: '.($browser->seeIn('body', 'Dashboard') ? 'Yes' : 'No')."\n";
            echo 'Page content contains Login: '.($browser->seeIn('body', 'Login') ? 'Yes' : 'No')."\n";
            echo 'Page content contains error: '.($browser->seeIn('body', 'error') ? 'Yes' : 'No')."\n";
        }
    });
});
