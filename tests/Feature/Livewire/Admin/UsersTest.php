<?php

use Livewire\Volt\Volt;

it('renders the users component', function () {
    Volt::test('admin.users.index')
        ->assertSee('Expected content in the users component');
});
