<?php

namespace App\Providers;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use App\Models\User;
use App\Observers\JobPostObserver;
use App\Observers\ResumeObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mail configuration
        Mail::extend('brevo', function () {
            return (new BrevoTransportFactory)->create(
                new Dsn(
                    'brevo+api',
                    'default',
                    config('services.brevo.key')
                )
            );
        });

        // Register model observers
        User::observe(UserObserver::class);
        JobPost::observe(JobPostObserver::class);
        Resume::observe(ResumeObserver::class);
    }
}
