<div>
    <x-header title="Available Job Posts">
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search jobs..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card>
        @if($jobs->count())
            <div class="grid gap-6 md:grid-cols-2">

                @foreach($jobs as $job)
                    <x-card class="shadow-lg">
                        <div class="font-bold text-xl">{{ $job->title }}</div>
                        <div class="text-sm text-gray-600">{{ $job->type }} • {{ $job->location }}</div>
                        <p class="mt-2">{{ Str::limit($job->description, 100) }}</p>
                        <div class="mt-3 text-xs text-gray-400">Deadline: {{ $job->deadline ?? 'N/A' }}</div>

                        <div class="mt-4 flex gap-2">
                            <x-button label="View" wire:click="viewJob({{ $job->id }})" sm />
                            <x-button label="Apply" wire:click="applyJob({{ $job->id }})" sm class="btn-primary" />
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
        @else
            <div class="p-6 text-center text-gray-500">No available jobs found.</div>
        @endif
    </x-card>

    {{-- View Job Modal --}}
    @if($viewingJob)
        <x-modal wire:model.defer="viewingJob">
            <x-slot name="title">{{ $selectedJob?->title }}</x-slot>
            <div>
                <div class="text-sm text-gray-500 mb-2">{{ $selectedJob?->type }} • {{ $selectedJob?->location }}</div>
                <div class="text-sm">{{ $selectedJob?->description }}</div>
                <div class="mt-4 text-xs text-gray-400">Deadline: {{ $selectedJob?->deadline ?? 'N/A' }}</div>
            </div>
        </x-modal>
    @endif

    {{-- Apply Modal --}}
    @if($applyingJob)
        <x-modal wire:model.defer="applyingJob">
            <x-form wire:submit="submitApplication">
                <x-slot name="title">Apply for: {{ $selectedJob?->title }}</x-slot>
                <div>
                    <div class="text-sm text-gray-500 mb-2">{{ $selectedJob?->type }} • {{ $selectedJob?->location }}</div>
                    <div class="text-sm mb-4">{{ $selectedJob?->description }}</div>
                    {{-- <x-file wire:model="resume" label="Upload Resume" /> --}}
                    <x-file wire:model="resume" label="Upload Resume" accept=".pdf,.doc,.docx" />

                    <x-button label="Submit Application" type="submit" class="mt-4 btn-primary" />
                </div>
            </x-form>
        </x-modal>
    @endif
</div>