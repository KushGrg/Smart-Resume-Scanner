<?php

use Livewire\Volt\Volt;

it('renders the login component', function () {
    Volt::test('pages.auth.login')
        ->assertSee('Enter your credentials to access your account');
});

it('validates login inputs', function () {
    Volt::test('pages.auth.login')
        ->set('email', '')
        ->set('password', '')
        ->call('login')
        ->assertHasErrors(['email', 'password']);
});

it('logs in with valid credentials', function () {
    $user = \App\Models\User::factory()->create([
        'email' => 'user@example.com',
        'password' => bcrypt('password'),
    ]);

    Volt::test('pages.auth.login')
        ->set('email', 'user@example.com')
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});
