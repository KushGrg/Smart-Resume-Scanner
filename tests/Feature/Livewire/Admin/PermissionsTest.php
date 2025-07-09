<?php

use Livewire\Volt\Volt;

it('renders the permissions component', function () {
    Volt::test('admin.permissions.index')
        ->assertSee('Expected content in the permissions component');
});
