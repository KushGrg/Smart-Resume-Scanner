<?php

use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

new class extends Component {
    use Toast;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public int $perPage = 10;
    
    // Form properties
    public ?int $editing_id = null;
    public string $name = '';
    public array $selected_permissions = [];
    
    public function mount()
    {
        $this->authorize('view roles');
    }
    
    public function with(): array
    {
        return [
            'roles' => $this->roles(),
            'headers' => $this->headers(),
            'permissions' => Permission::all()
        ];
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'permissions', 'label' => 'Permissions', 'sortable' => false],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-1 text-center', 'sortable' => false],
        ];
    }

    public function roles()
    {
        return Role::query()
            ->with('permissions')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function edit(Role $role): void
    {
        $this->authorize('edit roles');
        
        $this->editing_id = $role->id;
        $this->name = $role->name;
        $this->selected_permissions = $role->permissions->pluck('id')->toArray();
        
        $this->drawer = true;
    }

    public function create(): void
    {
        $this->authorize('create roles');
        
        $this->editing_id = null;
        $this->name = '';
        $this->selected_permissions = [];
        
        $this->drawer = true;
    }

    public function save(): void
    {
        if ($this->editing_id) {
            $this->authorize('edit roles');
            
            $role = Role::find($this->editing_id);
            $data = $this->validate([
                'name' => 'required|string|max:255|unique:roles,name,'.$this->editing_id,
                'selected_permissions' => 'array'
            ]);
            
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['selected_permissions']);
            
            $this->success('Role updated successfully');
        } else {
            $this->authorize('create roles');
            
            $data = $this->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'selected_permissions' => 'array'
            ]);
            
            $role = Role::create(['name' => $data['name']]);
            $role->syncPermissions($data['selected_permissions']);
            
            $this->success('Role created successfully');
        }
        
        $this->drawer = false;
    }

    public function delete(Role $role): void
    {
        $this->authorize('delete roles');
        
        if ($role->name === 'admin') {
            $this->error('Cannot delete admin role');
            return;
        }
        
        $role->delete();
        $this->success('Role deleted successfully');
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Roles Management" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create Role" class="btn-primary" @click="$wire.create()" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE -->
    <x-card>
        <x-table 
            :headers="$headers" 
            :rows="$roles" 
            :sort-by="$sortBy" 
            striped 
            with-pagination
            per-page="perPage"
            :per-page-values="[5, 10, 15, 25, 50]"
        >
            @scope('cell_permissions', $role)
                <div class="flex flex-wrap gap-1">
                    @foreach($role->permissions as $permission)
                        <x-badge :value="$permission->name" class="badge-ghost" />
                    @endforeach
                </div>
            @endscope
            
            @scope('actions', $role)
                <div class="flex justify-center gap-1">
                    <x-button icon="o-pencil" class="btn-ghost btn-sm" @click="$wire.edit({{ $role->id }})" />
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" 
                        @click="$wire.delete({{ $role->id }})"
                        wire:confirm.prompt="Are you sure?\nType DELETE to confirm|DELETE" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DRAWER FORM -->
    <x-drawer wire:model="drawer" title="{{ $editing_id ? 'Edit Role' : 'Create Role' }}" right separator with-close-button>
        <x-form wire:submit="save">
            <x-input label="Name" wire:model="name" />
            
            <x-choices 
                label="Permissions" 
                wire:model="selected_permissions" 
                :options="$permissions" 
                option-value="id"
                option-label="name"
                multiple
                searchable
                hint="Select permissions for this role"
            />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.drawer = false" />
                <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div> 