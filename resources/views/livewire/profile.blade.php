<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public $name;
    public $email;
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    public $message = '';
    public $messageType = '';

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
        ];
    }

    public function updateProfile()
    {
        $this->validate();

        $user = Auth::user();
        $emailChanged = $user->email !== $this->email;

        // Update user information
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        // If email is changed, mark it as unverified and update previously_verified
        if ($emailChanged) {
            $wasVerified = $user->hasVerifiedEmail();
            $updateData['email_verified_at'] = null;
            $updateData['previously_verified'] = $wasVerified || $user->previously_verified;
        }

        $user->update($updateData);

        // Update password if provided
        if ($this->new_password) {
            $user->update([
                'password' => bcrypt($this->new_password),
            ]);
        }

        // Send verification email and redirect if email was changed
        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }
        $this->message = 'Profile updated successfully!';
        $this->messageType = 'success';
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
    }

    public function resendVerification()
    {
        Auth::user()->sendEmailVerificationNotification();
        $this->message = 'Verification email sent!';
        $this->messageType = 'success';
    }
};

?>
<div>
    <x-card title="Profile Information" subtitle="Update your account's profile information and email address.">
        <x-form wire:submit="updateProfile">
            <x-input label="Name" wire:model="name" required />

            <x-input label="Email" type="email" wire:model="email" required />

            <x-badge :value="auth()->user()->hasVerifiedEmail() ? 'Verified' : 'Unverified'" :class="auth()->user()->hasVerifiedEmail() ? 'badge-success' : 'badge-warning'" />

            @unless (auth()->user()->hasVerifiedEmail())
                <x-button label="Resend Verification Email" wire:click="resendVerification" class="btn-ghost btn-sm" />
            @endunless
            <x-input label="Current Password" type="password" wire:model="current_password"
                hint="Leave empty if you don't want to change your password" />

            <x-input label="New Password" type="password" wire:model="new_password" hint="Minimum 8 characters" />

            <x-input label="Confirm New Password" type="password" wire:model="new_password_confirmation" />

            @if ($message)
                <x-alert :title="$message" :class="$messageType === 'success' ? 'alert-success' : 'alert-error'" icon="o-information-circle" />
            @endif

            <x-button label="Save Changes" class="btn-primary" type="submit" spinner="updateProfile" />
        </x-form>
    </x-card>
</div>
