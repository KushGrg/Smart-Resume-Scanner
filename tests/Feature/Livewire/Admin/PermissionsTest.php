<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get};

it('renders the permissions component', function () {
    Volt::test('admin.permissions.index')
        ->assertSee('Expected content in the permissions component');
}); 