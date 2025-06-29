<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

new
#[Layout('components.layouts.empty')]
#[Title('Registration')]
class extends Component {
    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|confirmed')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function register()
    {
        $data = $this->validate();

        $data['avatar'] = '/empty-user.jpg';
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        $user->assignRole('user');

        auth()->login($user);

        request()->session()->regenerate();

        $user->sendEmailVerificationNotification();

        // Redirect to verification notice page
        return redirect()->route('verification.notice');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10">
        <x-app-brand />
    </div>
 
    <x-card title="Register" subtitle="Create a new account to get started">
        <x-form wire:submit="register">
            <x-input placeholder="Name" wire:model="name" icon="o-user" />
            <x-input placeholder="E-mail" wire:model="email" icon="o-envelope" />
            <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" />
            <x-input placeholder="Confirm Password" wire:model="password_confirmation" type="password" icon="o-key" />
    
            <x-slot:actions>
                <x-button label="Already registered?" class="btn-ghost" link="/login" />
                <x-button label="Register" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="register" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
