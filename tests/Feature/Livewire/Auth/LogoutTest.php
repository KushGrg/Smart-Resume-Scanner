<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get};

it('renders the logout component', function () {
    Volt::test('auth.logout')
        ->assertSee('Expected content in the logout component');
});
