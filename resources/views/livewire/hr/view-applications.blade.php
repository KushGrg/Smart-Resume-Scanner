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
                <x-select 
                    label="Job Post" 
                    wire:model.live="selectedJobPost" 
                    :options="$jobPosts" 
                    option-value="id"
                    option-label="title"
                    placeholder="Select job post..."
                />
            </div>

            {{-- Status Filter --}}
            <div>
                <x-select 
                    label="Status" 
                    wire:model.live="statusFilter"
                    :options="[
                        ['id' => 'all', 'name' => 'All Statuses'],
                        ['id' => 'pending', 'name' => 'Pending'],
                        ['id' => 'reviewed', 'name' => 'Reviewed'],
                        ['id' => 'shortlisted', 'name' => 'Shortlisted'],
                        ['id' => 'rejected', 'name' => 'Rejected']
                    ]"
                    option-value="id"
                    option-label="name"
                />
            </div>

            {{-- Score Filter --}}
            <div>
                <x-input 
                    label="Min Score %" 
                    type="number" 
                    min="0" 
                    max="100" 
                    step="10"
                    wire:model.live="minScore"
                    placeholder="0"
                />
            </div>

            {{-- Per Page --}}
            <div>
                <x-select 
                    label="Per Page" 
                    wire:model.live="perPage"
                    :options="[
                        ['id' => 5, 'name' => '5'],
                        ['id' => 10, 'name' => '10'],
                        ['id' => 25, 'name' => '25'],
                        ['id' => 50, 'name' => '50']
                    ]"
                    option-value="id"
                    option-label="name"
                />
            </div>
        </div>
    </x-card>

    {{-- Applications Table --}}
    <x-card>
        @if($applications->count())
            <x-table 
                :headers="$headers" 
                :rows="$applications" 
                :sort-by="$sortBy" 
                with-pagination
                class="table-zebra"
            >
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
                        <x-badge 
                            value="{{ $application->status_display }}" 
                            class="badge-{{ $application->status_badge_color }}"
                        />
                    </div>
                @endscope

                @scope('cell_applied_at', $application)
                    <div>
                        <div class="font-medium">{{ $application->applied_at ? $application->applied_at->format('M d, Y') : $application->created_at->format('M d, Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $application->applied_at ? $application->applied_at->format('H:i') : $application->created_at->format('H:i') }}</div>
                    </div>
                @endscope

                @scope('cell_actions', $application)
                    <div class="flex gap-1">
                        <x-button 
                            icon="o-eye" 
                            wire:click="viewResume({{ $application->id }})"
                            tooltip="View Resume"
                            class="btn-sm btn-ghost"
                        />
                        <x-button 
                            icon="o-arrow-down-tray" 
                            wire:click="downloadResume({{ $application->id }})"
                            tooltip="Download Resume"
                            class="btn-sm btn-ghost"
                        />
                        <x-button 
                            icon="o-pencil" 
                            wire:click="openStatusModal({{ $application->id }})"
                            tooltip="Update Status"
                            class="btn-sm btn-ghost"
                        />
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
        <x-modal wire:model="viewingResume" max-width="6xl" title="Resume Details">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Resume Info Panel --}}
                <div class="lg:col-span-1 space-y-4">
                    <x-card class="bg-gray-50">
                        <h3 class="font-bold text-lg mb-4">Applicant Details</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Name</label>
                                <p class="font-medium">{{ $selectedResume->jobSeekerDetail->user->name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Email</label>
                                <p>{{ $selectedResume->jobSeekerDetail->user->email }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Job Applied</label>
                                <p class="font-medium">{{ $selectedResume->jobPost->title }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Applied Date</label>
                                <p>{{ $selectedResume->applied_at ? $selectedResume->applied_at->format('M d, Y H:i') : $selectedResume->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Score</label>
                                @if($selectedResume->similarity_score !== null)
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-lg {{ $this->getScoreColorClass($selectedResume->similarity_score) }}">
                                            {{ number_format($selectedResume->similarity_score * 100, 1) }}%
                                        </span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-3">
                                            <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" 
                                                 style="width: {{ $selectedResume->similarity_score * 100 }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-gray-400">N/A</p>
                                @endif
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Status</label>
                                <x-badge 
                                    value="{{ $selectedResume->status_display }}" 
                                    class="badge-{{ $selectedResume->status_badge_color }}"
                                />
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">File Info</label>
                                <p class="text-sm">{{ $selectedResume->file_name }}</p>
                                <p class="text-xs text-gray-500">{{ $selectedResume->file_size_formatted }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-2">
                            <x-button 
                                label="Update Status" 
                                wire:click="openStatusModal({{ $selectedResume->id }})"
                                class="w-full btn-primary"
                                icon="o-pencil"
                            />
                            <x-button 
                                label="Download Resume" 
                                wire:click="downloadResume({{ $selectedResume->id }})"
                                class="w-full btn-outline"
                                icon="o-arrow-down-tray"
                            />
                        </div>
                    </x-card>
                </div>

                {{-- Resume Preview Panel --}}
                <div class="lg:col-span-2">
                    <x-card>
                        <h3 class="font-bold text-lg mb-4">Resume Preview</h3>
                        <div class="h-[70vh]">
                            @if(pathinfo($selectedResume->file_path, PATHINFO_EXTENSION) === 'pdf')
                                <iframe 
                                    src="{{ asset('storage/' . $selectedResume->file_path) }}"
                                    class="w-full h-full border rounded-lg" 
                                    frameborder="0"
                                    allowfullscreen>
                                    This browser does not support PDF viewing. 
                                    <a href="{{ asset('storage/' . $selectedResume->file_path) }}" target="_blank" class="underline">
                                        Click here to download the PDF
                                    </a>.
                                </iframe>
                                <div class="text-xs text-gray-400 text-center mt-2">
                                    If the preview does not load, 
                                    <a href="{{ asset('storage/' . $selectedResume->file_path) }}" target="_blank" class="underline">
                                        click here to download the PDF
                                    </a>.
                                </div>
                            @else
                                <div class="flex items-center justify-center h-full bg-gray-50 rounded-lg">
                                    <div class="text-center">
                                        <x-icon name="o-document" class="w-20 h-20 text-gray-400 mx-auto mb-4" />
                                        <p class="text-lg font-medium text-gray-600 mb-2">Preview not available</p>
                                        <p class="text-gray-500 mb-4">Download the resume to view its contents</p>
                                        <x-button 
                                            label="Download Resume" 
                                            wire:click="downloadResume({{ $selectedResume->id }})"
                                            class="btn-primary"
                                            icon="o-arrow-down-tray"
                                        />
                                    </div>
                                </div>
                            @endif
                        </div>
                    </x-card>
                </div>
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
                        <x-badge 
                            value="{{ $selectedResume->status_display }}" 
                            class="badge-{{ $selectedResume->status_badge_color }}"
                        />
                    </p>
                </div>

                <x-select 
                    label="New Status" 
                    wire:model="newStatus"
                    :options="$statusOptions"
                    option-value="id"
                    option-label="name"
                    placeholder="Select new status..."
                    required
                />

                <div class="flex justify-end gap-4 mt-6">
                    <x-button label="Cancel" @click="$wire.statusModal = false" />
                    <x-button 
                        label="Update Status" 
                        wire:click="updateStatus" 
                        class="btn-primary"
                        spinner="updateStatus"
                    />
                </div>
            </div>
        </x-modal>
    @endif
</div>
