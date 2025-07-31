<?php

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
    #[Layout('components.layouts.empty')]
    #[Title('Login')]
    class extends Component {
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            request()->session()->regenerate();

            // Log successful login
            AuditLog::logSuccessfulLogin($user);

            // If email is not verified, redirect to verification page
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect()->intended('/dashboard');
        }

        // Log failed login attempt
        AuditLog::logFailedLogin($this->email);

        $this->addError('email', 'The provided credentials do not match our records.');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">


    <x-card w-full>
        <div class="flex items-center gap-2 mb-3 justify-center mb-6">
            {{-- <x-icon name="o-cube" class="w-6 -mb-1.5 text-purple-500 justify-center" /> --}}
            <span
                class="font-bold text-3xl me-3 bg-gradient-to-r from-purple-500 to-pink-300 bg-clip-text text-transparent ">
                Smart Resume Scanner
            </span>
        </div>
        {{-- <H2 class="mt-2 mb-3   text-center font-bold">Login</H2> --}}
        {{-- <Legend>Login</Legend> --}}
        <x-form wire:submit="login">
            <x-input placeholder="E-mail" wire:model="email" icon="o-envelope" />
            <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" />

            <div class="text-right mt-2">
                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:text-primary-focus">
                    Forgot your password?
                </a>
            </div>

            <x-slot:actions>
                <x-button label="Create an account" class="btn-ghost" link="/register" />
                <x-button label="Login" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>