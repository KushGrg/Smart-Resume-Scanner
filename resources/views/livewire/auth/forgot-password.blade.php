<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;

new
#[Layout('components.layouts.empty')]
#[Title('Forgot Password')]
class extends Component {
    public $email = '';
    public $status = null;

    protected function rules()
    {
        return ['email' => 'required|email'];
    }

    public function sendResetLink()
    {
        $this->validate();
        
        $status = Password::sendResetLink(
            ['email' => $this->email]
        );
        
        if ($status === Password::RESET_LINK_SENT) {
            $this->status = __($status);
            $this->email = '';
        } else {
            $this->addError('email', __($status));
        }
    }
};

?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10">
        <x-app-brand />
    </div>

    <x-card title="Forgot Password" subtitle="No problem. Just let us know your email address and we will email you a password reset link.">
        @if (session('status'))
            <x-alert type="success" message="{{ session('status') }}" class="mb-4" />
        @endif

        <x-form wire:submit="sendResetLink">
            <x-input placeholder="Email" type="email" wire:model="email" icon="o-envelope" required />

            <x-slot:actions>
                <x-button label="Back to login" class="btn-ghost" link="{{ route('login') }}" />
                <x-button label="Reset Password" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="sendResetLink" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div> 