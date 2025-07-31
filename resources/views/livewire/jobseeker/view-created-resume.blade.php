<div>
    <x-card>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
            <div class="flex-1">
                <x-input placeholder="Search resumes..." wire:model.debounce.500ms="search" class="w-full" />
            </div>
            <div class="flex items-center gap-2">
                <x-button class="btn bg-base-200" wire:click="$toggle('showTrashed')" :label="$showTrashed ? 'Show Active' : 'Show Trash'" />
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
                            wire:click="showResumeDetails({{ $resume->id }})" />
                        @if(!$showTrashed)
                            <x-button outline icon="o-trash" class="bg-red-600 text-white" label="Delete"
                                wire:click="deleteResume({{ $resume->id }})" />
                            {{-- <x-button outline icon="o-document-arrow-down" label="Download"
                                wire:click="downloadResume({{ $resume->id }})" /> --}}
                        @else
                            <x-button outline icon="o-arrow-path" label="Restore"
                                wire:click="restoreResume({{ $resume->id }})" />
                            <x-button outline icon="o-trash" label="Delete Permanently"
                                wire:click="forceDeleteResume({{ $resume->id }})"
                                wire:confirm="Are you sure you want to permanently delete this resume?" />
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center text-gray-500 py-10">No resumes found.</div>
            @endforelse
        </div>

        <x-modal wire:model="showResumeModal" class="backdrop-blur">
            <x-card title="Resume Details">
                @if($selectedResume)
                    <div class="space-y-4">
                        <h3 class="text-xl font-bold">{{ $selectedResume->name }}</h3>
                        <div class="text-gray-500">{{ $selectedResume->designation }}</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p><span class="font-semibold">Email:</span> {{ $selectedResume->email }}</p>
                                <p><span class="font-semibold">Phone:</span> {{ $selectedResume->phone }}</p>
                            </div>
                            <div>
                                <p><span class="font-semibold">Location:</span> {{ $selectedResume->city }},
                                    {{ $selectedResume->country }}
                                </p>
                            </div>
                        </div>
                        <div class="border-t pt-4">
                            <h4 class="font-semibold mb-2">Summary:</h4>
                            <p>{{ $selectedResume->summary }}</p>
                        </div>
                    </div>
                @endif

                <x-slot:actions>
                    <x-button label="Download" wire:click="downloadResume({{ $selectedResume->id ?? 0 }})"
                        icon="o-document-arrow-down" />
                    <x-button label="Close" wire:click="closeResumeModal" />
                </x-slot:actions>
            </x-card>
        </x-modal>
    </x-card>
</div>