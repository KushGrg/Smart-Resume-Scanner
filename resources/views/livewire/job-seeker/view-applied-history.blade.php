<div>
    <x-header title="Your Job Applications">
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search applications..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
    </x-header>

    <x-card>
        @if($applications->count())
            <div class="grid gap-6">
                @foreach($applications as $application)
                    <x-card class="shadow-lg">
                        <div class="font-bold text-xl">{{ $application->jobPost->title }}</div>
                        <div class="text-sm text-gray-600">
                            {{ $application->jobPost->type }} • {{ $application->jobPost->location }}
                        </div>
                        <div class="mt-2 text-sm">
                            <span class="font-medium">Applied on:</span>
                            {{ $application->created_at->format('M d, Y') }}
                        </div>
                        <div class="mt-2 text-sm">
                            <span class="font-medium">Status:</span>
                            <span
                                class="badge badge-{{ $application->application_status === 'pending' ? 'warning' : ($application->application_status === 'accepted' ? 'success' : 'error') }}">
                                {{ ucfirst($application->application_status) }}
                            </span>
                        </div>
                        <div class="mt-3">
                            <x-button label="View Resume" wire:click="viewResume({{ $application->id }})" sm />
                            <x-button label="Download Resume" wire:click="downloadResume({{ $application->id }})" sm />
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

    {{-- Resume View Modal --}}
    @if($viewingResume)
        <x-modal wire:model.defer="viewingResume" max-width="4xl">
            <x-slot name="title">Your Resume</x-slot>
            <div class="h-[80vh]">
                @if(pathinfo($selectedResume->file_path, PATHINFO_EXTENSION) === 'pdf')
                    <iframe src="{{ asset('storage/' . $selectedResume->file_path) }}" class="w-full h-full"
                        frameborder="0"></iframe>
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
</div>