<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;

new
#[Layout('components.layouts.empty')]
#[Title('Reset Password')]
class extends Component {
    public $email;
    public $password = '';
    public $password_confirmation = '';
    public $token;
    public $status = null;

    public function mount($token)
    {
        $this->email = request()->query('email', '');
        $this->token = $token;
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function resetPassword()
    {
        $this->validate();

        $status = Password::reset(
            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('status', __($status));
            $this->redirect(route('login'));
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

    <x-card title="Reset Password" subtitle="Enter your new password below.">
        <x-form wire:submit="resetPassword">
            <x-input placeholder="Email" type="email" wire:model="email" icon="o-envelope" required />
            <x-input placeholder="New Password" type="password" wire:model="password" icon="o-key" required />
            <x-input placeholder="Confirm New Password" type="password" wire:model="password_confirmation" icon="o-key" required />

            <x-slot:actions>
                <x-button label="Back to login" class="btn-ghost" link="{{ route('login') }}" />
                <x-button label="Reset Password" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="resetPassword" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div> 