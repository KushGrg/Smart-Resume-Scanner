<?php

use App\Livewire\Hr\JobPost;
use App\Livewire\Hr\ViewApplications;
use App\Livewire\JobSeeker\AvailableJobs;
use App\Livewire\JobSeeker\CreateProfile;
use App\Livewire\JobSeeker\ViewAppliedHistory;
use App\Livewire\JobSeeker\ViewCreatedResume;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

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
    $user = User::findOrFail($id);

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
    Volt::route('/hr/dashboard', 'hr_dashboard')->name('dashboard')->middleware('permission:access dashboard');
    Volt::route('/job_seeker/dashboard', 'dashboard')->name('dashboard')->middleware('permission:access dashboard');
    Volt::route('/logout', 'auth.logout')->name('logout');
    Route::get('/hr/jobpost', JobPost::class)->name('hr.jobpost.index')->middleware('permission:view job posts');
    Route::get('/hr/applications', ViewApplications::class)->name('hr.applications.index')->middleware('permission:view job posts');
    Route::get('available-jobs', AvailableJobs::class)->name('job_seeker.available_jobs.index')->middleware('permission:view available jobs');
    Route::get('view-applied-history', ViewAppliedHistory::class)->name('view_applied_history.index')->middleware('permission:view applied history');
    Route::get('create-profile', CreateProfile::class)->name('create_profile.index')->middleware('permission:create profile');
    Route::get('view-created-resume-list', ViewCreatedResume::class)->name('view_created_resume.index')->middleware('permission:view applied resume job posts');
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
