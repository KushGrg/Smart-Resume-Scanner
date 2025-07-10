<?php

namespace App\Providers;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use App\Models\User;
use App\Observers\JobPostObserver;
use App\Observers\ResumeObserver;
use App\Observers\UserObserver;
use App\Policies\JobPostPolicy;
use App\Policies\ResumePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
        // Register Resume Processing Services
        $this->app->singleton(\App\Services\ResumeRanker::class, function ($app) {
            return new \App\Services\ResumeRanker;
        });

        $this->app->singleton(\App\Services\TextExtractionService::class, function ($app) {
            return new \App\Services\TextExtractionService;
        });

        $this->app->singleton(\App\Services\BatchResumeProcessor::class, function ($app) {
            return new \App\Services\BatchResumeProcessor(
                $app->make(\App\Services\ResumeRanker::class)
            );
        });
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

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(JobPost::class, JobPostPolicy::class);
        Gate::policy(Resume::class, ResumePolicy::class);
    }
}
