<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Log::info('New user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Log email verification
        if ($user->wasChanged('email_verified_at') && $user->email_verified_at) {
            Log::info('User email verified', [
                'user_id' => $user->id,
                'email' => $user->email,
                'verified_at' => $user->email_verified_at,
            ]);
        }

        // Log email changes
        if ($user->wasChanged('email')) {
            Log::info('User email changed', [
                'user_id' => $user->id,
                'old_email' => $user->getOriginal('email'),
                'new_email' => $user->email,
            ]);
        }

        // Update previously_verified flag
        if ($user->wasChanged('email_verified_at') && $user->email_verified_at && ! $user->previously_verified) {
            $user->previously_verified = true;
            $user->saveQuietly(); // Save without triggering events
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        Log::info('User account deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Log::info('User account restored', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        Log::info('User account permanently deleted', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
