<div>

    <!-- TABLE -->
    <x-card>
        <!-- HEADER -->
        <x-header title="Job Posts Management" separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-input placeholder="Search..." wire:model.live.debounce="search" clearable
                    icon="o-magnifying-glass" />
            </x-slot:middle>
            <x-slot:actions>
                <x-button label="Create Job Post" class="btn-primary" @click="$wire.create()" />
            </x-slot:actions>
        </x-header>

        <x-table :headers="$headers" :rows="$jobPosts" :sort-by="$sortBy" striped with-pagination per-page="perPage"
            :per-page-values="[5, 10, 15, 25, 50]">
            @scope('status', $jobPost)
            <x-badge :value="ucwords($jobPost->status)" @class([
                'badge-success' => $jobPost->status === 'active',
                'badge-error' => $jobPost->status === 'inactive'
            ]) />
            @endscope

            @scope('actions', $jobPost)
            <div class="flex justify-center gap-1">
                <x-button icon="o-pencil" class="btn-ghost btn-sm" @click="$wire.edit({{ $jobPost->id }})" />
                <x-button icon="o-trash" class="btn-ghost btn-sm text-error" @click="$wire.delete({{ $jobPost->id }})"
                    wire:confirm.prompt="Are you sure?\nType DELETE to confirm|DELETE" />
            </div>
            @endscope
        </x-table>
    </x-card>

    <!-- DRAWER FORM -->
    <x-drawer wire:model="drawer" class="w-11/12 lg:w-2/5"
        title="{{ $editing_id ? 'Edit Job Post' : 'Create Job Post' }}" right separator with-close-button>
        <x-form wire:submit="save">
            <x-input label="Title" wire:model="title" />
            <x-textarea label="Description" wire:model="description" rows="5" />
            <x-input label="Location" wire:model="location" />

            <x-select label="Job Type" wire:model="type" :options="$jobTypes" />

            <x-datepicker label="Deadline" wire:model="deadline" />
            <x-textarea label="Requirements" wire:model="requirements" rows="3" />
            <x-input label="Experience Level" wire:model="experience_level" />
            <x-input type=number label=" Min Salary" wire:model="min_salary" />
            <x-input type=number label=" Max Salary" wire:model="max_salary" />



            <x-select label="Status" wire:model="status" :options="$statuses" />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.drawer = false" />
                <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-drawer>
</div>