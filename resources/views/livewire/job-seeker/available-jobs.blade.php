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
                        <div class="mt-3 text-xs text-gray-400">
                            Deadline: {{ $job->deadline?->format('Y-m-d') ?? 'N/A' }}
                        </div>
                        {{-- <div class="mt-3 text-xs text-gray-400">
                            Requirements:{{$job->requirements}}
                            dd($job->requirements)
                        </div> --}}

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
            <x-slot name="title" class="text-xl font-bold">{{ $selectedJob?->title }}</x-slot>
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <x-icon name="o-briefcase" class="w-4 h-4" />
                    <span>{{ $selectedJob?->type }}</span>
                    <x-icon name="o-map-pin" class="w-4 h-4 ml-2" />
                    <span>{{ $selectedJob?->location }}</span>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <x-icon name="o-calendar" class="w-4 h-4" />
                    <span>Deadline: {{ $selectedJob?->deadline?->format('Y-m-d') ?? 'N/A' }}</span>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h3 class="font-semibold text-gray-700">Job Description</h3>
                    <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">{{ $selectedJob?->description }}</p>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <h3 class="font-semibold text-gray-700">Requirements</h3>
                    <ul class="mt-1 text-sm text-gray-600 list-disc list-inside">
                        @foreach(explode("\n", $selectedJob?->requirements) as $requirement)
                            @if(trim($requirement))
                                <li>{{ trim($requirement) }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>


            </div>
        </x-modal>
    @endif

    {{-- Apply Modal --}}
    @if($applyingJob)
        <x-modal wire:model.defer="applyingJob">
            <x-form wire:submit="submitApplication">
                <x-slot name="title" class="text-xl font-bold">Apply for: {{ $selectedJob?->title }}</x-slot>
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <x-icon name="o-briefcase" class="w-4 h-4" />
                        <span>{{ $selectedJob?->type }}</span>
                        <x-icon name="o-map-pin" class="w-4 h-4 ml-2" />
                        <span>{{ $selectedJob?->location }}</span>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="font-semibold text-gray-700">Job Description</h3>
                        <p class="mt-1 text-sm text-gray-600 whitespace-pre-line">{{ $selectedJob?->description }}</p>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <h3 class="font-semibold text-gray-700">Requirements</h3>
                        <ul class="mt-1 text-sm text-gray-600 list-disc list-inside">
                            @foreach(explode("\n", $selectedJob?->requirements) as $requirement)
                                @if(trim($requirement))
                                    <li>{{ trim($requirement) }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <x-file wire:model="resume" label="Upload Resume" accept=".pdf,.doc,.docx" hint="PDF Only (Max 2MB)"
                            class="mt-4" />
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                        <x-button label="Cancel" @click="$wire.applyingJob = false" />
                        <x-button label="Submit Application" type="submit" class="btn-primary" />
                    </div>
                </div>
            </x-form>
        </x-modal>
    @endif
</div>