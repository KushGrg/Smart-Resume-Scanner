<?php

use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
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
    
    public function mount()
    {
        $this->authorize('view permissions');
    }
    
    public function with(): array
    {
        return [
            'permissions' => $this->permissions(),
            'headers' => $this->headers(),
        ];
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-1', 'sortable' => false],
        ];
    }

    public function permissions()
    {
        return Permission::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function edit(Permission $permission): void
    {
        $this->authorize('edit permissions');
        
        $this->editing_id = $permission->id;
        $this->name = $permission->name;
        
        $this->drawer = true;
    }

    public function create(): void
    {
        $this->authorize('create permissions');
        
        $this->editing_id = null;
        $this->name = '';
        
        $this->drawer = true;
    }

    public function save(): void
    {
        if ($this->editing_id) {
            $this->authorize('edit permissions');
            
            $permission = Permission::find($this->editing_id);
            $data = $this->validate([
                'name' => 'required|string|max:255|unique:permissions,name,'.$this->editing_id,
            ]);
            
            $permission->update($data);
            
            $this->success('Permission updated successfully');
        } else {
            $this->authorize('create permissions');
            
            $data = $this->validate([
                'name' => 'required|string|max:255|unique:permissions,name',
            ]);
            
            Permission::create($data);
            
            $this->success('Permission created successfully');
        }
        
        $this->drawer = false;
    }

    public function delete(Permission $permission): void
    {
        $this->authorize('delete permissions');
        
        if (in_array($permission->name, ['access dashboard'])) {
            $this->error('Cannot delete system permissions');
            return;
        }
        
        $permission->delete();
        $this->success('Permission deleted successfully');
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Permissions Management" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create Permission" class="btn-primary" @click="$wire.create()" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE -->
    <x-card>
        <x-table 
            :headers="$headers" 
            :rows="$permissions" 
            :sort-by="$sortBy" 
            striped 
            with-pagination
            per-page="perPage"
            :per-page-values="[5, 10, 15, 25, 50]"
        >
            @scope('actions', $permission)
                <div class="flex gap-1">
                    <x-button icon="o-pencil" class="btn-ghost btn-sm" @click="$wire.edit({{ $permission->id }})" />
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" 
                        @click="$wire.delete({{ $permission->id }})"
                        wire:confirm.prompt="Are you sure?\nType DELETE to confirm|DELETE" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DRAWER FORM -->
    <x-drawer wire:model="drawer" title="{{ $editing_id ? 'Edit Permission' : 'Create Permission' }}" right separator with-close-button>
        <x-form wire:submit="save">
            <x-input label="Name" wire:model="name" />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.drawer = false" />
                <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div> 