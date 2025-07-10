<?php

use Laravel\Dusk\Browser;

test('debug register page', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
            ->screenshot('register-page-debug')
            ->dump();
    });
});
