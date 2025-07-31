<?php

use App\Models\Hr\HrDetail as HrDetails;
use App\Models\JobSeeker\JobSeekerDetail as JobSeekerDetails;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new App\Models\Hr\HrDetail;
new App\Models\JobSeeker\JobSeekerDetail;

new
    #[Layout('components.layouts.empty')]
    #[Title('Registration')]
    class extends Component {
    use WithFileUploads;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|confirmed')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    public $roles = [];

    public $photo;

    public $phone;

    public $organization_name;

    public $designation;

    #[Rule('required')]
    public $role = 'user';

    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
        // Load roles using Spatie's Role model
        $this->roles = \Spatie\Permission\Models\Role::where('name', '!=', 'admin')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->name,
                    'name' => ucwords($role->name),
                ];
            })
            ->toArray();
    }

    public function register()
    {
        $data = $this->validate();
        // dd($data);
        $data['avatar'] = '/empty-user.jpg';
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        // dd($user);

        // If user is job_seeker
        if ($data['role'] == 'job_seeker') {
            // Save Job seeker details
            JobSeekerDetails::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'current_designation' => $this->designation,
            ]);
        }
        // Save HR Details if role is HR
        if ($data['role'] === 'hr') {
            $logoPath = null;
            if ($this->photo) {
                $logoPath = $this->photo->store('logos', 'public');
            }

            HrDetails::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'organization_name' => $this->organization_name,
                'phone' => $user->phone,
                'logo' => $logoPath,
            ]);
        }

        // Assign the selected role to the user
        $user->syncRoles([$data['role']]);

        auth()->login($user);

        request()->session()->regenerate();

        $user->sendEmailVerificationNotification();

        // Redirect to verification notice page
        return redirect()->route('verification.notice');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    {{-- <div class="mb-10">
        <x-app-brand />
    </div> --}}

    <x-card>
        <div class="flex items-center gap-2 mb-3 justify-center mb-6">
            {{-- <x-icon name="o-cube" class="w-6 -mb-1.5 text-purple-500 justify-center" /> --}}
            <span
                class="font-bold text-3xl me-3 bg-gradient-to-r from-purple-500 to-pink-300 bg-clip-text text-transparent ">
                Smart Resume Scanner
            </span>
        </div>
        <x-form wire:submit="register">

            <x-radio label="Select Role" wire:model="role" :options="$roles" inline />
            <x-input placeholder="Name" wire:model="name" icon="o-user" />
            <x-input placeholder="E-mail" wire:model="email" icon="o-envelope" />
            <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" />
            <x-input placeholder="Confirm Password" wire:model="password_confirmation" type="password" icon="o-key" />
            <x-input type='number' placeholder="Phone Number" wire:model="phone" min:8 max:10 />
            <div wire:show="role==='job_seeker'">
                <x-input placeholder=" Your Designation" wire:model="designation" />
            </div>
            <div wire:show="role === 'hr'">
                <x-input placeholder="Organization Name" wire:model="organization_name" class="mb-3" />
                <x-file wire:model="photo" accept="image/png, image/jpeg" class="rounded-md" />

                <div class="mt-2">

                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Uploaded Photo"
                            class="w-full h-32 object-cover rounded-md">
                    @endif
                </div>

            </div>

</div>

<x-slot:actions>
    <x-button label="Already registered?" class="btn-ghost" link="/login" />
    <x-button label="Register" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="register" />
</x-slot:actions>
</x-form>
</x-card>
</div>