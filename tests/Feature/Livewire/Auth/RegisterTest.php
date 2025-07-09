<?php

use Livewire\Volt\Volt;

it('renders the register component', function () {
    Volt::test('auth.register')
        ->assertSee('Expected content in the register component');
});
