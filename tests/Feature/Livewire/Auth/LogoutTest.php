<?php

use Livewire\Volt\Volt;

it('renders the logout component', function () {
    Volt::test('auth.logout')
        ->assertSee('Expected content in the logout component');
});
