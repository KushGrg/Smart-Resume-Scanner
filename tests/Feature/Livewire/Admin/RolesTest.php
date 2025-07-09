<?php

use Livewire\Volt\Volt;

it('renders the roles component', function () {
    Volt::test('admin.roles.index')
        ->assertSee('Expected content in the roles component');
});
