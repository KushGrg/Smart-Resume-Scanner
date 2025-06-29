<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.empty')]
#[Title('Welcome')]
class extends Component {
    public function mount()
    {
        // No additional logic needed for now
    }
}; ?>

<div class="min-h-screen flex flex-col items-center justify-center p-4">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-4">Welcome to Our Platform</h1>
        <p class="text-lg text-gray-600">Get started with your journey today</p>
        @if (session('verified'))
            <div class="mt-4">
                <x-alert title="{{ session('verified') }}" class="alert-success" icon="o-check" />
            </div>
        @endif
    </div>

    <div class="flex flex-col md:flex-row gap-4">
        @auth
            <x-button label="Go to Dashboard" link="/dashboard" icon="o-chart-bar" class="btn-primary" />
            <x-button label="Logout" link="/logout" icon="o-arrow-right-on-rectangle" class="btn-ghost" />
        @else
            <x-button label="Login" link="/login" icon="o-arrow-right-on-rectangle" class="btn-primary" />
            <x-button label="Register" link="/register" icon="o-user-plus" class="btn-ghost" />
        @endauth
    </div>
</div> 