<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get};

it('renders the register component', function () {
    Volt::test('auth.register')
        ->assertSee('Expected content in the register component');
});
