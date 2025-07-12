<div class="max-w-6xl mx-auto p-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div class="flex-1">
            <x-input placeholder="Search resumes..." wire:model.debounce.500ms="search" class="w-full" />
        </div>
        <div class="flex items-center gap-2">
            <x-button wire:click="$toggle('showTrashed')" :label="$showTrashed ? 'Show Active' : 'Show Trash'" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($resumes as $resume)
            <div class=" shadow rounded-lg p-5 flex flex-col justify-between relative">
                <div>
                    <h3 class="text-lg font-bold mb-1">{{ $resume->name }}</h3>
                    <div class="text-sm text-gray-500 mb-2">{{ $resume->designation }}</div>
                    <div class="mb-2">
                        <span class="font-semibold">Email:</span> {{ $resume->email }}<br>
                        <span class="font-semibold">Phone:</span> {{ $resume->phone }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Location:</span> {{ $resume->city }}, {{ $resume->country }}
                    </div>
                    <div class="mb-2">
                        <span class="font-semibold">Summary:</span> {{ Str::limit($resume->summary, 80) }}
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <x-button outline icon="o-eye" label="View Details"
                        wire:click="emitShowResumeDetails({{ $resume->id }})" />
                    <x-button outline icon="o-pencil" label="Edit" :href="route('create_profile.index', ['resume_id' => $resume->id])" />
                    @if(!$showTrashed)
                        <x-button outline icon="o-trash" label="Delete" wire:click="deleteResume({{ $resume->id }})" />
                    @else
                        <x-button outline icon="o-arrow-path" label="Restore" wire:click="restoreResume({{ $resume->id }})" />
                    @endif
                    <x-button outline icon="o-document-arrow-down" label="Download PDF"
                        wire:click="downloadResume({{ $resume->id }})" />
                    <x-button outline icon="o-share" label="Share" wire:click="copyShareLink({{ $resume->id }})" />
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 py-10">No resumes found.</div>
        @endforelse
    </div>

    {{-- Resume Details Modal (Livewire event-driven, can use Mary UI modal) --}}
    <livewire:job-seeker.resume-details-modal />
</div>