<div>
    <x-header title="Job Applications" subtitle="Review and manage candidate applications">
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search applications..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Clear Filters" wire:click="clearFilters" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    {{-- Filters Section --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Job Post Filter --}}
            <div>
                <x-select label="Job Post" wire:model.live="selectedJobPost" :options="$jobPosts" option-value="id"
                    option-label="title" placeholder="Select job post..." />
            </div>

            {{-- Status Filter --}}
            <div>
                <x-select label="Status" wire:model.live="statusFilter" :options="[
        ['id' => 'all', 'name' => 'All Statuses'],
        ['id' => 'pending', 'name' => 'Pending'],
        ['id' => 'reviewed', 'name' => 'Reviewed'],
        ['id' => 'shortlisted', 'name' => 'Shortlisted'],
        ['id' => 'rejected', 'name' => 'Rejected']
    ]"
                    option-value="id" option-label="name" />
            </div>

            {{-- Score Filter --}}
            {{-- <div>
                <x-input label="Min Score %" type="number" min="0" max="100" step="10" wire:model.live="minScore"
                    placeholder="0" />
            </div> --}}

            {{-- Per Page --}}
            <div>
                <x-select label="Per Page" wire:model.live="perPage" :options="[
        ['id' => 5, 'name' => '5'],
        ['id' => 10, 'name' => '10'],
        ['id' => 25, 'name' => '25'],
        ['id' => 50, 'name' => '50']
    ]" option-value="id"
                    option-label="name" />
            </div>
        </div>
    </x-card>

    {{-- Applications Table --}}
    <x-card>
        @if($applications->count())
            <x-table :headers="$headers" :rows="$applications" :sort-by="$sortBy" with-pagination class="table-zebra">
                @scope('cell_job_post.title', $application)
                <div class="font-medium">{{ $application->jobPost->title }}</div>
                <div class="text-xs text-gray-500">ID: {{ $application->jobPost->id }}</div>
                @endscope

                @scope('cell_job_seeker_detail.user.name', $application)
                <div class="font-medium">{{ $application->jobSeekerDetail->user->name }}</div>
                <div class="text-xs text-gray-500">{{ $application->jobSeekerDetail->user->email }}</div>
                @endscope

                @scope('cell_similarity_score', $application)
                <div class="text-center">
                    @if($application->similarity_score !== null)
                        <span class="{{ $this->getScoreColorClass($application->similarity_score) }}">
                            {{ number_format($application->similarity_score * 100, 1) }}%
                        </span>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                style="width: {{ $application->similarity_score * 100 }}%"></div>
                        </div>
                    @else
                        <span class="text-gray-400">N/A</span>
                    @endif
                </div>
                @endscope

                @scope('cell_application_status', $application)
                <div class="text-center">
                    <x-badge value="{{ $application->status_display }}"
                        class="badge-{{ $application->status_badge_color }}" />
                </div>
                @endscope

                @scope('cell_applied_at', $application)
                <div>
                    <div class="font-medium">{{ $application->applied_at->format('M d, Y')}} </div>
                    {{-- <div class="text-xs text-gray-500">{{ $application->applied_at->format('M d, Y') }}</div> --}}
                </div>
                @endscope

                @scope('cell_actions', $application)
                <div class="flex gap-1">
                    <x-button icon="o-eye" wire:click="viewResume({{ $application->id }})" tooltip="View Resume"
                        class="btn-sm btn-ghost" />
                    <x-button icon="o-arrow-down-tray" wire:click="downloadResume({{ $application->id }})"
                        tooltip="Download Resume" class="btn-sm btn-ghost" />
                    <x-button icon="o-pencil" wire:click="openStatusModal({{ $application->id }})" tooltip="Update Status"
                        class="btn-sm btn-ghost" />
                </div>
                @endscope
            </x-table>

            <div class="mt-4">
                {{ $applications->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                <x-icon name="o-document-text" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                <h3 class="text-lg font-medium mb-2">No Applications Found</h3>
                <p>No job applications match your current filters.</p>
            </div>
        @endif
    </x-card>

    {{-- Resume View Modal --}}
    @if($viewingResume && $selectedResume)
        <x-modal wire:model="viewingResume" max-width="7xl" title="Resume Details" persistent
            x-on:click.outside="$wire.viewingResume = false" class="backdrop-blur-sm">
            <div class="space-y-8">

                {{-- Applicant Info + Application Details --}}
                <x-card class="shadow-sm p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

                        {{-- Applicant Profile --}}
                        <div class="flex items-center gap-4">
                            <div
                                class="w-20 h-20 rounded-full bg-primary text-white flex items-center justify-center text-2xl font-bold">
                                {{ strtoupper(substr($selectedResume->jobSeekerDetail->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-xl">{{ $selectedResume->jobSeekerDetail->user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $selectedResume->jobSeekerDetail->user->email }}</p>
                            </div>
                        </div>

                        {{-- Job Info --}}
                        <div class="space-y-2">
                            <h4 class="text-gray-600 font-medium">Application</h4>
                            <div class="text-sm text-gray-500">Job Post</div>
                            <div class="font-semibold">{{ $selectedResume->jobPost->title }}</div>

                            <div class="text-sm text-gray-500 mt-2">Applied On</div>
                            <div>
                                {{ $selectedResume->applied_at?->format('M d, Y H:i') ?? $selectedResume->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>

                        {{-- Score + Status --}}
                        <div class="flex flex-col items-start gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Status</div>
                                <x-badge value="{{ $selectedResume->status_display }}"
                                    class="badge-{{ $selectedResume->status_badge_color }} text-base" />
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Match Score</div>
                                @if($selectedResume->similarity_score !== null)
                                    <span
                                        class="text-2xl font-bold {{ $this->getScoreColorClass($selectedResume->similarity_score) }}">
                                        {{ number_format($selectedResume->similarity_score * 100, 1) }}%
                                    </span>
                                @else
                                    <p class="text-gray-400">N/A</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </x-card>

                {{-- Resume File + Actions --}}
                <x-card class="shadow-sm p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-3 p-4 bg-white rounded-lg border w-full md:w-auto flex-grow">
                            <x-icon name="o-document-text" class="w-8 h-8 text-gray-400" />
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $selectedResume->file_name }}</p>
                                <p class="text-xs text-gray-500">{{ $selectedResume->file_size_formatted }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <x-button label="Update Status" wire:click="openStatusModal({{ $selectedResume->id }})"
                                class="btn-primary" icon="o-pencil" />
                            <x-button label="Download" wire:click="downloadResume({{ $selectedResume->id }})"
                                class="btn-outline" icon="o-arrow-down-tray" />
                            <x-button label="Close" wire:click="$set('viewingResume', false)" class="btn-ghost"
                                icon="o-x-mark" />
                        </div>
                    </div>
                </x-card>

                {{-- Resume Preview --}}
                <x-card class="shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold">Resume Preview</h3>
                        <div class="flex gap-2">
                            <x-button icon="{{ $fullscreenPreview ? 'o-arrows-pointing-in' : 'o-arrows-pointing-out' }}"
                                wire:click="toggleFullscreen"
                                tooltip="{{ $fullscreenPreview ? 'Exit Fullscreen' : 'Fullscreen' }}"
                                class="btn-sm btn-ghost" />
                            <x-button icon="o-arrow-down-tray" wire:click="downloadResume({{ $selectedResume->id }})"
                                tooltip="Download" class="btn-sm btn-ghost" />
                        </div>
                    </div>

                    <div
                        class="{{ $fullscreenPreview ? 'fixed inset-0 z-50 bg-white p-4' : 'h-[75vh] bg-gray-50 rounded-lg border' }} overflow-hidden relative">
                        @if(pathinfo($selectedResume->file_path, PATHINFO_EXTENSION) === 'pdf')
                            <iframe src="{{ asset('storage/' . $selectedResume->file_path) }}#toolbar=0&navpanes=0"
                                class="w-full h-full border-0" allowfullscreen>
                            </iframe>
                        @else
                            <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                                <x-icon name="o-document" class="w-16 h-16 text-gray-400 mb-4" />
                                <h4 class="text-xl font-medium text-gray-600 mb-2">Preview Not Available</h4>
                                <p class="text-gray-500 mb-6">This file format cannot be previewed in the browser</p>
                                <x-button label="Download Resume" wire:click="downloadResume({{ $selectedResume->id }})"
                                    class="btn-primary" icon="o-arrow-down-tray" />
                            </div>
                        @endif

                        @if($fullscreenPreview)
                            <div class="absolute top-4 right-4">
                                <x-button icon="o-x-mark" wire:click="toggleFullscreen" tooltip="Close Fullscreen"
                                    class="btn-sm btn-ghost" />
                            </div>
                        @endif
                    </div>
                </x-card>

            </div>
        </x-modal>
    @endif

    {{-- Status Update Modal --}}
    @if($statusModal && $selectedResume)
        <x-modal wire:model="statusModal" max-width="md" title="Update Application Status" persistent>
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-medium mb-2">Application Details</h4>
                    <p class="text-sm text-gray-600">Applicant: {{ $selectedResume->jobSeekerDetail->user->name }}</p>
                    <p class="text-sm text-gray-600">Job: {{ $selectedResume->jobPost->title }}</p>
                    <p class="text-sm text-gray-600">Current Status:
                        <x-badge value="{{ $selectedResume->status_display }}"
                            class="badge-{{ $selectedResume->status_badge_color }}" />
                    </p>
                </div>

                <x-select label="New Status" wire:model="newStatus" :options="$statusOptions" option-value="id"
                    option-label="name" placeholder="Select new status..." required />

                <div class="flex justify-end gap-4 mt-6">
                    <x-button label="Cancel" @click="$wire.statusModal = false" />
                    <x-button label="Update Status" wire:click="updateStatus" class="btn-primary" spinner="updateStatus" />
                </div>
            </div>
        </x-modal>
    @endif
</div>