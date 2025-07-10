<?php

use Livewire\Volt\Volt;

it('renders the landing component', function () {
    // Test the Volt component directly
    Volt::test('pages.landing')
        ->assertSee('Welcome to Our Platform')
        ->assertSee('Get started with your journey today')
        ->assertSee('Login')
        ->assertSee('Register');
});

it('checks if login button works', function () {
    // Test navigation to login from landing component
    Volt::test('pages.landing')
        ->assertSee('Login')
        ->call('goToLogin')
        ->assertRedirect('/login');
});

it('checks if register button works', function () {
    // Test navigation to register from landing component
    Volt::test('pages.landing')
        ->assertSee('Register')
        ->call('goToRegister')
        ->assertRedirect('/register');
});

it('renders login component when navigated', function () {
    // Test the login component directly
    Volt::test('pages.auth.login')
        ->assertSee('Enter your credentials to access your account');
});

it('renders register component when navigated', function () {
    // Test the register component directly
    Volt::test('pages.auth.register')
        ->assertSee('Create a new account to get started');
});
