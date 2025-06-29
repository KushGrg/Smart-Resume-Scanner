<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

new class extends Component {
    use Toast;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public int $perPage = 10;
    
    // Form properties
    public ?int $editing_id = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public array $selected_roles = [];
    
    public function mount()
    {
        $this->authorize('view users');
    }
    
    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
            'roles' => Role::all()
        ];
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'roles', 'label' => 'Roles', 'sortable' => false],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-1 text-center', 'sortable' => false],
        ];
    }

    public function users()
    {
        return User::query()
            ->with('roles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function edit(User $user): void
    {
        $this->authorize('edit users');
        
        $this->editing_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selected_roles = $user->roles->pluck('id')->toArray();
        $this->password = '';
        
        $this->drawer = true;
    }

    public function create(): void
    {
        $this->authorize('create users');
        
        $this->editing_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->selected_roles = [];
        
        $this->drawer = true;
    }

    public function save(): void
    {
        if ($this->editing_id) {
            $this->authorize('edit users');
            
            $user = User::find($this->editing_id);
            $data = $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,'.$this->editing_id,
                'password' => 'nullable|min:8',
                'selected_roles' => 'array'
            ]);
            
            $user->update(array_filter([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'] ? bcrypt($data['password']) : null,
            ]));
            
            $user->syncRoles($data['selected_roles']);
            
            $this->success('User updated successfully');
        } else {
            $this->authorize('create users');
            
            $data = $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'selected_roles' => 'array'
            ]);
            
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
            
            $user->syncRoles($data['selected_roles']);
            
            $this->success('User created successfully');
        }
        
        $this->drawer = false;
    }

    public function delete(User $user): void
    {
        $this->authorize('delete users');
        
        $user->delete();
        $this->success('User deleted successfully');
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Users Management" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create User" class="btn-primary" @click="$wire.create()" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE -->
    <x-card>
        <x-table 
            :headers="$headers" 
            :rows="$users" 
            :sort-by="$sortBy" 
            striped 
            with-pagination
            per-page="perPage"
            :per-page-values="[5, 10, 15, 25, 50]"
        >
            @scope('cell_roles', $user)
                @foreach($user->roles as $role)
                    <x-badge :value="$role->name" class="badge-ghost" />
                @endforeach
            @endscope
            
            @scope('actions', $user)
                <div class="flex justify-center gap-1">
                    <x-button icon="o-pencil" class="btn-ghost btn-sm" @click="$wire.edit({{ $user->id }})" />
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" 
                        @click="$wire.delete({{ $user->id }})"
                        wire:confirm.prompt="Are you sure?\nType DELETE to confirm|DELETE" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DRAWER FORM -->
    <x-drawer wire:model="drawer" title="{{ $editing_id ? 'Edit User' : 'Create User' }}" right separator with-close-button>
        <x-form wire:submit="save">
            <x-input label="Name" wire:model="name" />
            <x-input label="Email" wire:model="email" type="email" />
            <x-input label="Password" wire:model="password" type="password" :required="!$editing_id" />
            
            <x-choices 
                label="Roles" 
                wire:model="selected_roles" 
                :options="$roles" 
                option-value="id"
                option-label="name"
                multiple
                searchable
                hint="Select one or more roles"
            />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.drawer = false" />
                <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div> 