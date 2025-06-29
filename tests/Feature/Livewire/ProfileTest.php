<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get};

it('renders the profile component', function () {
    Volt::test('profile')
        ->assertSee('Expected content in the profile component');
}); 