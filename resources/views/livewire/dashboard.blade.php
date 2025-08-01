<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use App\Models\JobSeeker\Resume;
use App\Models\Hr\JobPost;
use Illuminate\Support\Facades\Auth;

new
    #[Layout('components.layouts.app')]
    #[Title('Dashboard')]
    class extends Component {
    public $totalAvailableJobs = 0;
    public $totalAppliedJobs = 0;
    public $totalCreatedResumes = 0;
    public $recentApplications = [];
    public $recentAvailableJobs = [];

    public function mount()
    {
        $this->authorize('access dashboard');
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();

        // Total available jobs (active)
        $this->totalAvailableJobs = JobPost::where('status', 'active')->count();

        // Total applied jobs by this user
        $this->totalAppliedJobs = Resume::where('user_id', $userId)->count();

        // Total created resumes by this user
        $this->totalCreatedResumes = Resume::where('user_id', $userId)
            ->distinct('file_path')
            ->count();

        // Recent applications (last 5)
        $this->recentApplications = Resume::where('user_id', $userId)
            ->with('jobPost')
            ->latest()
            ->limit(5)
            ->get();

        // Recent available jobs (last 5)
        $this->recentAvailableJobs = JobPost::where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Job Seeker Dashboard</h1>
            <p class="text-gray-600 mt-1">Overview of your job search activities</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-stat title="Available Jobs" :value="$totalAvailableJobs" icon="o-briefcase"
            tooltip="Total active job posts available" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white" />

        <x-stat title="Applied Jobs" :value="$totalAppliedJobs" icon="o-document-check"
            tooltip="Total jobs you've applied to" class="bg-gradient-to-r from-green-500 to-green-600 text-white" />

        <x-stat title="Created Resumes" :value="$totalCreatedResumes" icon="o-document-text"
            tooltip="Total resumes you've uploaded" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white" />
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Recent Applications -->
        <x-card title="Recent Applications">
            @if($recentApplications->count() > 0)
                <div class="space-y-3">
                    @foreach($recentApplications as $application)
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $application->jobPost->title ?? 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    Applied {{ $application->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <x-badge :value="ucwords($application->application_status ?? 'pending')"
                                class="badge-{{ $application->application_status === 'shortlisted' ? 'success' : ($application->application_status === 'rejected' ? 'error' : 'warning') }}" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-icon name="o-inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                    <p>No applications yet</p>
                </div>
            @endif
        </x-card>

        <!-- Recent Available Jobs -->
        <x-card title="Recent Job Openings">
            @if($recentAvailableJobs->count() > 0)
                <div class="space-y-3">
                    @foreach($recentAvailableJobs as $job)
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $job->title }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ $job->location }} • {{ ucwords($job->type) }}
                                </p>
                            </div>
                            <x-button label="Apply" link="/available-jobs" class="btn-sm btn-primary" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-icon name="o-briefcase" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                    <p>No available jobs found</p>
                </div>
            @endif
        </x-card>
    </div>

    <!-- Quick Actions -->
    <x-card title="Quick Actions" class="bg-gradient-to-r from-gray-50 to-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- <x-button label="Browse Jobs" link="/job-seeker/available-jobs" class="btn-primary"
                icon="o-magnifying-glass" /> --}}
            <x-button label="Create Resume" link="/create-profile" class="btn bg-green-500" icon="o-document-plus" />
            <x-button label="Application History" link="/view-created-resume-list" class="btn bg-blue-500"
                icon="o-clock" />
        </div>
    </x-card>
</div>