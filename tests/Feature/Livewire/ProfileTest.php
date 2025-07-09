<?php

use Livewire\Volt\Volt;

it('renders the profile component', function () {
    Volt::test('profile')
        ->assertSee('Expected content in the profile component');
});
