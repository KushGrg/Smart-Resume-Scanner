<?php

use Livewire\Volt\Volt;
use function Pest\Laravel\{get, post};

it('renders the landing component', function () {
    // Simulate a request to the landing page
    $response = get('/');

    // Assert that the response status is 200 (OK)
    $response->assertStatus(200);

    // Assert that the landing component is rendered
    $response->assertSeeLivewire('landing');

    // Assert that specific content is visible on the page
    $response->assertSee('Welcome to Our Platform');
    $response->assertSee('Get started with your journey today');
    $response->assertSee('Login');
    $response->assertSee('Register');
});

it('checks if login button works', function () {
    $response = get('/');
    $response->assertSee('Login');
    $response->assertSee('Register');

    // Simulate clicking the login button
    $response = get('/login');
    $response->assertStatus(200);
    $response->assertSee('Enter your credentials to access your account');
});

it('checks if register button works', function () {
    $response = get('/');
    $response->assertSee('Register');

    // Simulate clicking the register button
    $response = get('/register');
    $response->assertStatus(200);
    $response->assertSee('Create a new account to get started');
});