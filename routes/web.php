<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;

// Landing page - accessible to all
Volt::route('/', 'landing')->name('landing');

// Authentication routes
Route::middleware('guest')->group(function () {
    Volt::route('/login', 'auth.login')->name('login');
    Volt::route('/register', 'auth.register')->name('register');
    Volt::route('/forgot-password', 'auth.forgot-password')->name('password.request');
    Volt::route('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
});

Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Http\Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);
    
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        throw new AuthorizationException;
    }

    if ($user->hasVerifiedEmail()) {
        return redirect('/');
    }

    $user->markEmailAsVerified();
    $user->previously_verified = true;
    $user->save();

    if (!Auth::check()) {
        $message = $user->previously_verified
        ? 'Welcome back! Your new email address has been verified.'
        : 'Email verification completed successfully!';
        Auth::login($user);
    } else {
        $message = $user->previously_verified
        ? 'New Email address has been verified for ' . $user->name . '.'
        : 'Email verification completed successfully for ' . $user->name . '.';
    }
    
    $user->sendEmailVerificationNotification();

    return redirect('/')->with('verified', $message);
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// Email verification routes
Route::middleware('auth')->group(function () {
    Volt::route('/email/verify', 'auth.verify-email')->name('verification.notice');
});

// Routes that require authentication but not email verification
Route::middleware('auth')->group(function () {
    Volt::route('/profile', 'profile')->name('profile');
    Volt::route('/dashboard', 'dashboard')->name('dashboard')->middleware('permission:access dashboard');
    Volt::route('/logout', 'auth.logout')->name('logout');
});

// Protected routes requiring email verification
Route::middleware(['auth', 'verified'])->group(function () {

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Volt::route('/users', 'admin.users.index')->name('users.index');
        Volt::route('/roles', 'admin.roles.index')->name('roles.index');
        Volt::route('/permissions', 'admin.permissions.index')->name('permissions.index');
    });
});
