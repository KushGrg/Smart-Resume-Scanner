<div>


    <x-card>
        <div>
            <x-header title="Your Job Applications">
                <x-slot:middle class="!justify-end">
                    <x-input placeholder="Search applications..." wire:model.live.debounce="search" clearable
                        icon="o-magnifying-glass" />
                </x-slot:middle>
            </x-header>
        </div>
        <x-card class="inset-shadow-2xs shadow-lg">
            @if($applications->count())
                <div class="grid gap-6">
                    @foreach($applications as $application)
                        <x-card>
                            <div class="font-semibold text-xl">{{ $application->jobPost->title }}</div>
                            <div class="text-sm text-gray-600 mt-2">
                                <x-icon name="o-map-pin" class="w-4 h-4 " />
                                {{ $application->jobPost->type }}
                                <x-icon name="o-briefcase" class="w-4 h-4 " />
                                {{ $application->jobPost->location }}
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="font-medium">Applied on:</span>
                                {{ $application->created_at->format('M d, Y') }}
                            </div>
                            <div class="mt-2 text-sm">
                                <span class="font-medium">Status:</span>
                                <span
                                    class="text-white badge badge-{{ $application->application_status === 'pending' ? 'warning' : ($application->application_status === 'accepted' ? 'success' : 'error') }}">
                                    {{ ucfirst($application->application_status) }}
                                </span>
                            </div>
                            <div class="mt-2 text-sm ">
                                <span class="font-medium">Score:</span>
                                @if(isset($application->similarity_score) && $application->similarity_score !== null)
                                    <span class="badge badge-info text-white">
                                        {{ number_format($application->similarity_score * 100, 1) }}%
                                    </span>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </div>
                            <div class="mt-3 flex gap-2">
                                <x-button icon="o-eye" label="View Resume" wire:click="viewResume({{ $application->id }})"
                                    class="bg-blue-600 rounded-md text-white" tooltip="View resume " sm />
                                <x-button icon="o-arrow-down-tray" label="Download Resume"
                                    wire:click="downloadResume({{ $application->id }})" tooltip="Download resume"
                                    class="bg-green-600 rounded-md text-white" sm />
                                <x-button label="Delete" class="btn bg-red-600 rounded-md text-white" icon="o-trash"
                                    wire:click="confirmDelete({{ $application->id }})" tooltip="Delete resume " sm />
                            </div>
                        </x-card>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $applications->links() }}
                </div>
            @else
                <div class="p-6 text-center text-gray-500">You haven't applied to any jobs yet.</div>
            @endif
        </x-card>
    </x-card>

    {{-- Resume View Modal --}}
    @if($viewingResume)
        <x-modal wire:model="viewingResume" max-width="4xl">
            <x-slot name="title">Your Resume</x-slot>
            <div class="h-[80vh]">
                @if(pathinfo($selectedResume->file_path, PATHINFO_EXTENSION) === 'pdf')
                    <iframe src="{{ asset('storage/' . $selectedResume->file_path) }}" class="w-full h-full" frameborder="0"
                        allowfullscreen>
                        This browser does not support PDF viewing. <a
                            href="{{ asset('storage/' . $selectedResume->file_path) }}" target="_blank" class="underline">Click
                            here to download the PDF</a>.
                    </iframe>
                    <div class="text-xs text-gray-400 text-center mt-2">If the preview does not load, <a
                            href="{{ asset('storage/' . $selectedResume->file_path) }}" target="_blank" class="underline">click
                            here to download the PDF</a>.</div>
                @else
                    <div class="flex items-center justify-center h-full">
                        <x-icon name="o-document" class="w-16 h-16 text-gray-400" />
                        <div class="ml-4">
                            <p class="text-lg">Download your resume to view it:</p>
                            <a href="{{ asset('storage/' . $selectedResume->file_path) }}" download
                                class="btn btn-primary mt-2">
                                Download Resume
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </x-modal>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($confirmingDelete)
        <x-modal wire:model="confirmingDelete" max-width="sm" title="Delete Resume" persistent>
            <div class="py-6 text-center">
                <x-icon name="o-trash" class="w-12 h-12 text-error mx-auto mb-4" />
                <div class="text-lg font-semibold mb-2">Are you sure you want to delete this resume?</div>
                {{-- <div class="text-gray-500 mb-4">This action cannot be undone.</div> --}}
                <div class="flex justify-center gap-4 mt-6">
                    <x-button label="Cancel" class="btn bg-blue-500 rounded-md" @click="$wire.confirmingDelete = false" />
                    <x-button label="Delete" class="btn bg-red-600 rounded-md" icon="o-trash" wire:click="deleteResume"
                        spinner />
                </div>
            </div>
        </x-modal>
    @endif
</div>