<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
#[Layout('components.layouts.empty')]
#[Title('Verify Email')]
class extends Component {
    public function resendVerification()
    {
        auth()->user()->sendEmailVerificationNotification();
        session()->flash('message', 'Verification link sent!');
    }
    
    public function logout()
    {
        auth()->logout();
        
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect('/');
    }

    public function mount()
    {
        if(auth()->user()->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10">
        <x-app-brand />
    </div>
 
    <x-card title="Verify Your Email" subtitle="{{ auth()->user()->previously_verified ? 'You\'ve changed your email address! Please verify your new email address before regaining access to your account.' : 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?' }}">
        @if (session('message'))
            <x-alert title="{{ session('message') }}" class="alert-success" icon="o-check" />
        @endif

        <p class="text-sm text-gray-600 mt-4">
            If you didn't receive the email, we will gladly send you another.
        </p>

        <div class="mt-4">
            <x-button 
                label="Resend Verification Email" 
                wire:click="resendVerification" 
                class="btn-primary"
                spinner="resendVerification"
            />
        </div>

        <div class="mt-4">
            <x-button 
                label="Log Out" 
                wire:click="logout" 
                class="btn-ghost"
                spinner="logout"
            />
        </div>
    </x-card>
</div>
