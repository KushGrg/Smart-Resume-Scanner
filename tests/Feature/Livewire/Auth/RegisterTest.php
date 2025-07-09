<?php

use Livewire\Volt\Volt;

it('renders the register component', function () {
    Volt::test('auth.register')
        ->assertSee('Registration')
        ->assertSee('Phone Number')
        ->assertSee('Create Account');
});
