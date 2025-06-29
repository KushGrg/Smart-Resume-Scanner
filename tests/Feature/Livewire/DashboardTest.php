<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get};

it('renders the dashboard component', function () {
    Volt::test('dashboard')
        ->assertSee('Expected content in the dashboard component');
}); 