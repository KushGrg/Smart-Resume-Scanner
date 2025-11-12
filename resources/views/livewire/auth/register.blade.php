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

    #[Rule('required|string|max:255')]
    public string $name = '';

    #[Rule('required|email|unique:users|regex:/^[^\d][\w.-]*@[\w.-]+\.[a-zA-Z]{2,}$/')]
    public string $email = '';

    #[Rule('required|confirmed|min:8')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    public $roles = [];

    //#[Rule('required_if:role,hr|image|mimes:jpg,jpeg,png|max:2048')]
    public $photo;

    #[Rule('required|numeric|digits_between:8,10')]
    public $phone;

    #[Rule('required_if:role,hr|max:255')]
    public $organization_name = '';

    #[Rule('required_if:role,job_seeker|string|max:255')]
    public $designation = '';

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

        $data['organization_name'] = $this->organization_name;
        $data['designation'] = $this->designation;
        // $data['photo'] = $this->photo;

        // $data['avatar'] = '/empty-user.jpg';
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

    <x-card class="shadow-xl">
        <x-app-brand />
        <x-card title="Smart Resume Scanner" class="text-center">
            <x-form wire:submit="register">
                <x-radio label="Select Role" wire:model="role" :options="$roles" inline omitError="true" />
                <x-input placeholder=" Name" wire:model="name" icon="o-user" errorField="name" firstErrorOnly="true" />
                <x-input placeholder="E-mail" wire:model="email" icon="o-envelope" errorField="email"
                    firstErrorOnly="true" />
                <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" errorField="password"
                    firstErrorOnly="true" />
                <x-input placeholder="Confirm Password" wire:model="password_confirmation" type="password"
                    icon="o-key" />
                <x-input type='number' placeholder="Phone Number" wire:model="phone" errorField="phone"
                    firstErrorOnly="true" icon="o-phone" />
                <div wire:show="role==='job_seeker'">
                    <x-input placeholder=" Your Designation" wire:model="designation" errorField="designation"
                        firstErrorOnly="true" icon="o-identification" />
                </div>
                <div wire:show="role === 'hr'">
                    <x-input placeholder="Organization Name" wire:model="organization_name" icon="o-building-office" />
                    <x-file wire:model="photo" accept="image/png, image/jpeg" class="rounded-md mt-4" />

                    <div class="mt-2">

                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" alt="Uploaded Photo"
                                class="w-full h-32 object-cover rounded-md">
                        @endif
                    </div>
                </div>

                <x-slot:actions>
                    <x-button label="Already registered?" class="btn-ghost" link="/login" />
                    <x-button label="Register" type="submit" icon="o-paper-airplane" class="btn-primary"
                        spinner="register" />
                </x-slot:actions>
            </x-form>
        </x-card>
    </x-card>
</div>