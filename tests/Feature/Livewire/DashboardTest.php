<?php

use Livewire\Volt\Volt;

it('renders the dashboard component', function () {
    Volt::test('dashboard')
        ->assertSee('Expected content in the dashboard component');
});
