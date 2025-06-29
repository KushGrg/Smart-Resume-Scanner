<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get};

it('renders the users component', function () {
    Volt::test('admin.users.index')
        ->assertSee('Expected content in the users component');
}); 