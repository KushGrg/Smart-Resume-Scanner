<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\HrDetail;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $name;
    public $email;
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    public $message = '';
    public $messageType = '';
    public $organization_name;
    public $phone;  
    public $logo;

    public function mount()
    {
        
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        if($user->hasRole('hr')) {
        $this->organization_name = $user->hrDetail->organization_name ?? '';
        $this->logo = $user->hrDetail->logo ?? '';
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            'current_password' => 'required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
            'organization_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|max:2048', // 2MB max
        ];
    }

    public function updateProfile()
    {
        $this->validate();

        $user = Auth::user();
        $emailChanged = $user->email !== $this->email;

        // Update user basic info
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,

            
        ];

        if ($emailChanged) {
            $wasVerified = $user->hasVerifiedEmail();
            $updateData['email_verified_at'] = null;
            $updateData['previously_verified'] = $wasVerified || $user->previously_verified;
        }

        $user->update($updateData);

        // Update password if needed
        if ($this->new_password) {
            $user->update([
                'password' => bcrypt($this->new_password),
            ]);
        }

        // HR-specific info
        if ($user->hasRole('hr')) {
            $logoPath = $user->hrDetail->logo ?? null;

            if ($this->logo && is_object($this->logo)) {
                $logoPath = $this->logo->store('logos', 'public');
            }

            App\Models\Hr\HrDetail::updateOrCreate(
                ['hid' => $user->id],
                [
                    'name' => $this->name,
                    'email' => $this->email,
                    'orgainzation_name' => $this->organization_name,
                    'phone' => $this->phone,
                    'logo' => $logoPath,
                ]
            );
        }
        else{
            App\Models\Job_seeker\Job_seeker_details::updateOrCreate(
                ['jid' => $user->id],
                [
                    
                    'phone' => $this->phone,
                ]
            );
        }

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

    <x-badge :value="auth()->user()->hasVerifiedEmail() ? 'Verified' : 'Unverified'" 
             :class="auth()->user()->hasVerifiedEmail() ? 'badge-success' : 'badge-warning'" />

    @unless (auth()->user()->hasVerifiedEmail())
        <x-button label="Resend Verification Email" wire:click="resendVerification" class="btn-ghost btn-sm" />
    @endunless

    <x-input label="Current Password" type="password" wire:model="current_password"
             hint="Leave empty if you don't want to change your password" />
    <x-input label="New Password" type="password" wire:model="new_password" hint="Minimum 8 characters" />
    <x-input label="Confirm New Password" type="password" wire:model="new_password_confirmation" />
    <x-input label="Phone Number" wire:model="phone" type="tel" />

   
    @if(auth()->user()->hasRole('hr'))
        <x-input label="Organization Name" wire:model="organization_name" />
        
        <x-file label="Logo" wire:model="logo" />

        @if ($logo)
            <img src="{{ $logo instanceof \Livewire\TemporaryUploadedFile ? $logo->temporaryUrl() : asset('storage/' . $logo) }}"
                class="w-32 h-32 object-cover rounded-md mt-2">
        @endif
    @endif

    @if ($message)
        <x-alert :title="$message" 
                 :class="$messageType === 'success' ? 'alert-success' : 'alert-error'" 
                 icon="o-information-circle" />
    @endif

    <x-button label="Save Changes" class="btn-primary mt-4" type="submit" spinner="updateProfile" />
</x-form>

    </x-card>
</div>

