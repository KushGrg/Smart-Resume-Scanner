<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get, post};

it('renders the login component', function () {
    $response = get('/login');
    $response->assertStatus(200);
    $response->assertSee('Enter your credentials to access your account');
});

it('validates login inputs', function () {
    $response = post('/login', [
        'email' => '',
        'password' => '',
    ]);

    $response->assertSessionHasErrors(['email', 'password']);
});

it('logs in with valid credentials', function () {
    $response = post('/login', [
        'email' => 'user@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
});
