<?php

use Livewire\Volt\Component;
use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new class extends Component {
    public $totalJobPosts = 0;
    public $activeJobPosts = 0;
    public $totalApplications = 0;
    public $pendingApplications = 0;
    public $shortlistedApplications = 0;
    public $rejectedApplications = 0;
    public $averageScore = 0;
    public $recentApplications = [];
    public $topJobPosts = [];
    public $applicationTrends = [];
    public $statusDistribution = [];

    public function mount()
    {
        $this->authorize('view job posts');
        \Log::info('Loading HR Dashboard', ['user_id' => auth()->id()]);
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $userId = Auth::id();
        \Log::debug('Loading dashboard data', ['user_id' => $userId]);

        // Job Posts Statistics - Filter by current HR user only
        $jobPostsQuery = JobPost::where('user_id', $userId);
        $this->totalJobPosts = $jobPostsQuery->count();
        $this->activeJobPosts = $jobPostsQuery->where('status', 'active')->count();

        // Applications Statistics - Filter by current HR user's job posts
        $applicationsQuery = Resume::whereHas('jobPost', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });

        // Count applications with fresh queries to ensure real-time status
        $this->totalApplications = $applicationsQuery->count();
        $this->pendingApplications = $applicationsQuery->clone()->where('application_status', 'pending')->count();
        $this->shortlistedApplications = $applicationsQuery->clone()->where('application_status', 'shortlisted')->count();
        $this->rejectedApplications = $applicationsQuery->clone()->where('application_status', 'rejected')->count();

        // Recent Applications (Last 5)
        $this->recentApplications = $applicationsQuery
            ->with(['jobPost', 'jobSeekerDetail.user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Top Job Posts by Applications
        $this->topJobPosts = JobPost::where('user_id', $userId)
            ->withCount('resumes')
            ->orderBy('resumes_count', 'desc')
            ->limit(5)
            ->get();

        // Application Trends (Last 7 days)
        $this->applicationTrends = $this->getApplicationTrends($userId);

        // Status Distribution
        $this->statusDistribution = $applicationsQuery
            ->select('application_status', DB::raw('count(*) as count'))
            ->groupBy('application_status')
            ->get()
            ->pluck('count', 'application_status')
            ->toArray();

        // Add debug logging for status counts
        \Log::debug('Application status counts', [
            'pending' => $this->pendingApplications,
            'shortlisted' => $this->shortlistedApplications,
            'rejected' => $this->rejectedApplications
        ]);
    }

    private function getApplicationTrends($userId)
    {
        $trends = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Resume::whereHas('jobPost', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
                ->whereDate('created_at', $date->toDateString())
                ->count();

            $trends[] = [
                'date' => $date->format('M d'),
                'count' => $count
            ];
        }
        return $trends;
    }

    public function getStatusBadgeColor($status)
    {
        return match ($status) {
            'pending' => 'warning',
            'reviewed' => 'info',
            'shortlisted' => 'success',
            'rejected' => 'error',
            default => 'neutral'
        };
    }

    public function getScoreColorClass($score)
    {
        if ($score === null)
            return 'text-gray-400';

        return match (true) {
            $score >= 0.8 => 'text-green-600',
            $score >= 0.6 => 'text-blue-600',
            $score >= 0.4 => 'text-yellow-600',
            default => 'text-red-600'
        };
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->dispatch('dashboard-refreshed');
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">HR Dashboard</h1>
            <p class="text-gray-600 mt-1">Overview of your job posts and applications</p>
        </div>
        <x-button label="Refresh Data" wire:click="refreshData" class="btn-outline" icon="o-arrow-path"
            spinner="refreshData" />
    </div>

    <!-- Stats Cards Row 1 - Enhanced -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Job Posts with trend indicator -->
        <x-stat title="Total Job Posts" :value="$totalJobPosts" icon="o-briefcase"
            tooltip="Total number of job posts created" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white"
            :trend="$totalJobPosts > 0 ? 'up' : 'neutral'" trend-text="From last week" />

        <!-- Active Job Posts with status indicator -->
        <x-stat title="Active Posts" :value="$activeJobPosts" icon="o-check-circle"
            tooltip="Currently active job posts ({{ $totalJobPosts > 0 ? number_format(($activeJobPosts / $totalJobPosts) * 100, 0) : 0 }}% of total)"
            class="bg-gradient-to-r from-green-500 to-green-600 text-white" :indicator="$activeJobPosts > 0 ? 'active' : 'inactive'" />

        <!-- Total Applications with comparison -->
        <x-stat title="Total Applications" :value="$totalApplications" icon="o-document-text"
            tooltip="Total applications received ({{ $totalJobPosts > 0 ? number_format($totalApplications / $totalJobPosts, 1) : 0 }} per job)"
            class="bg-gradient-to-r from-purple-500 to-purple-600 text-white"
            :comparison="$applicationTrends[6]['count'] > 0 ? number_format(($totalApplications - $applicationTrends[6]['count']) / $applicationTrends[6]['count'] * 100, 0) : 100"
            comparison-text="vs last week" />
    </div>

    <!-- Stats Cards Row 2 - Enhanced -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pending Applications with urgency indicator -->
        <x-stat title="Pending Review" :value="$pendingApplications" icon="o-clock"
            tooltip="Applications awaiting review ({{ $totalApplications > 0 ? number_format(($pendingApplications / $totalApplications) * 100, 0) : 0 }}% of total)"
            class="bg-gradient-to-r from-amber-400 to-amber-500 text-white" :indicator="$pendingApplications > 10 ? 'high' : ($pendingApplications > 0 ? 'medium' : 'low')" />

        <!-- Shortlisted with conversion rate -->
        <x-stat title="Shortlisted" :value="$shortlistedApplications" icon="o-heart"
            tooltip="Shortlisted candidates ({{ $totalApplications > 0 ? number_format(($shortlistedApplications / $totalApplications) * 100, 0) : 0 }}% conversion)"
            class="bg-gradient-to-r from-emerald-500 to-emerald-600 text-white" :trend="$shortlistedApplications > 0 ? 'up' : 'neutral'" />

        <!-- Rejected with quality control indicator -->
        <x-stat title="Rejected" :value="$rejectedApplications" icon="o-x-circle"
            tooltip="Rejected applications (quality control)"
            class="bg-gradient-to-r from-red-500 to-red-600 text-white" :indicator="$rejectedApplications > 0 ? 'active' : 'inactive'" />
    </div>

    <!-- Charts and Tables Row -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Application Trends Chart -->
        <x-card title="Application Trends (Last 7 Days)" class="h-fit">
            <x-slot:menu>
                <x-icon name="o-chart-bar" class="w-5 h-5" />
            </x-slot:menu>

            <div class="h-64 flex items-end justify-between space-x-2 px-2">
                @php
                    $maxCount = collect($applicationTrends)->max('count') ?: 1;
                @endphp
                @foreach($applicationTrends as $trend)
                    <div class="flex flex-col items-center flex-1">
                        @php
                            $height = $trend['count'] > 0 ? max(20, ($trend['count'] / $maxCount) * 200) : 10;
                        @endphp
                        <div class="bg-blue-500 rounded-t hover:bg-blue-600 transition-colors duration-200 w-full flex items-end justify-center text-white text-xs font-medium"
                            style="height: {{ $height }}px">
                            @if($trend['count'] > 0)
                                {{ $trend['count'] }}
                            @endif
                        </div>
                        <div class="text-xs text-gray-600 mt-2 font-medium">{{ $trend['date'] }}</div>
                    </div>
                @endforeach
            </div>

            @if(collect($applicationTrends)->sum('count') == 0)
                <div class="text-center py-8 text-gray-500">
                    <x-icon name="o-chart-bar" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                    <p>No applications in the last 7 days</p>
                </div>
            @endif
        </x-card>

        <!-- Status Distribution -->
        <x-card title="Application Status Distribution">
            <x-slot:menu>
                <x-icon name="o-chart-pie" class="w-5 h-5" />
            </x-slot:menu>

            @if($totalApplications > 0)
                <div class="space-y-4">
                    @foreach(['pending' => 'Pending', 'reviewed' => 'Reviewed', 'shortlisted' => 'Shortlisted', 'rejected' => 'Rejected'] as $status => $label)
                        @php $count = $statusDistribution[$status] ?? 0; @endphp
                        @php $percentage = $totalApplications > 0 ? ($count / $totalApplications) * 100 : 0; @endphp

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <x-badge :value="$label" class="badge-{{ $this->getStatusBadgeColor($status) }}" />
                                <span class="text-sm font-medium">{{ $count }}</span>
                            </div>
                            <div class="flex items-center space-x-2 flex-1 max-w-32">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $this->getStatusBadgeColor($status) == 'warning' ? 'yellow' : ($this->getStatusBadgeColor($status) == 'info' ? 'blue' : ($this->getStatusBadgeColor($status) == 'success' ? 'green' : 'red')) }}-500 h-2 rounded-full transition-all duration-300"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 w-10 text-right">{{ number_format($percentage, 1) }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-icon name="o-chart-pie" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                    <p>No applications to display</p>
                </div>
            @endif
        </x-card>
    </div>

    <!-- Data Tables Row -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Recent Applications -->
        <x-card title="Recent Applications" class="h-fit">
            <x-slot:menu>
                <x-button label="View All" link="/hr/applications" class="btn-sm btn-outline" />
            </x-slot:menu>

            @if($recentApplications->count() > 0)
                <div class="space-y-3">
                    @foreach($recentApplications as $application)
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <x-icon name="o-user" class="w-5 h-5 text-blue-600" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $application->jobSeekerDetail->user->name }}
                                </p>
                                <p class="text-xs text-gray-600 truncate">
                                    {{ $application->jobPost->title }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($application->similarity_score)
                                    <span
                                        class="text-xs font-medium {{ $this->getScoreColorClass($application->similarity_score) }}">
                                        {{ number_format($application->similarity_score * 100, 1) }}%
                                    </span>
                                @endif
                                <x-badge :value="ucwords($application->application_status)"
                                    class="badge-{{ $this->getStatusBadgeColor($application->application_status) }} text-xs" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-icon name="o-inbox" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                    <p>No recent applications</p>
                </div>
            @endif
        </x-card>

        <!-- Top Job Posts by Applications -->
        <x-card title="Top Job Posts by Applications">
            <x-slot:menu>
                <x-button label="Manage Posts" link="/hr/jobpost" class="btn-sm btn-outline" />
            </x-slot:menu>

            @if($topJobPosts->count() > 0)
                <div class="space-y-3">
                    @foreach($topJobPosts as $index => $jobPost)
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-green-600">#{{ $index + 1 }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">
                                    {{ $jobPost->title }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ $jobPost->location }} • {{ ucwords($jobPost->type) }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                {{-- <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">{{ $jobPost->resumes_count }}</p>
                                    <p class="text-xs text-gray-600">applications</p>
                                </div> --}}
                                <x-badge :value="ucwords($jobPost->status)" @class([
                                    'badge-success' => $jobPost->status === 'active',
                                    'badge-error' => $jobPost->status === 'inactive'
                                ]) />
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <x-icon name="o-briefcase" class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                    <p>No job posts created yet</p>
                    <x-button label="Create Your First Job Post" link="/hr/job-posts" class="btn-primary btn-sm mt-2" />
                </div>
            @endif
        </x-card>
        <x-card title="Quick Actions" class="bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-button label="Create Job Post" link="/hr/jobpost" class="btn-primary" icon="o-plus" />
                <x-button label="View Applications" link="/hr/applications" class="btn-outline" icon="o-eye" />

            </div>
        </x-card>
    </div>

    <!-- Quick Actions -->
</div>