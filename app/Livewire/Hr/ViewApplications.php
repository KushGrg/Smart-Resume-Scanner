<?php

namespace App\Livewire\Hr;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class ViewApplications extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public array $sortBy = ['column' => 'similarity_score', 'direction' => 'desc'];

    // Filter properties
    public ?int $selectedJobPost = null;

    public string $statusFilter = 'all';

    public float $minScore = 0.0;

    // Modal/drawer properties
    public bool $viewingResume = false;

    public bool $statusModal = false;

    public ?Resume $selectedResume = null;

    public string $newStatus = '';

    // Status options
    public array $statusOptions = [
        ['id' => 'pending', 'name' => 'Pending'],
        ['id' => 'reviewed', 'name' => 'Reviewed'],
        ['id' => 'shortlisted', 'name' => 'Shortlisted'],
        ['id' => 'rejected', 'name' => 'Rejected'],
    ];

    public function mount()
    {
        $this->authorize('view job posts');
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'job_post.title', 'label' => 'Job', 'sortable' => true],
            ['key' => 'job_seeker_detail.user.name', 'label' => 'Applicant', 'sortable' => true],
            ['key' => 'similarity_score', 'label' => 'Score', 'sortable' => true, 'class' => 'text-center'],
            ['key' => 'application_status', 'label' => 'Status', 'sortable' => true, 'class' => 'text-center'],
            ['key' => 'applied_at', 'label' => 'Applied', 'sortable' => true],
            ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-1 text-center', 'sortable' => false],
        ];
    }

    public function getJobPostsProperty()
    {
        return JobPost::where('user_id', Auth::id())
            ->select('id', 'title')
            ->orderBy('title')
            ->get()
            ->prepend((object) ['id' => null, 'title' => 'All Job Posts']);
    }

    public function applications()
    {
        $query = Resume::query()
            ->with(['jobPost', 'jobSeekerDetail.user'])
            ->whereHas('jobPost', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('jobPost', function ($subQ) {
                        $subQ->where('title', 'like', '%'.$this->search.'%');
                    })
                        ->orWhereHas('jobSeekerDetail.user', function ($subQ) {
                            $subQ->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->selectedJobPost, function ($query) {
                $query->where('job_post_id', $this->selectedJobPost);
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('application_status', $this->statusFilter);
            })
            ->when($this->minScore > 0, function ($query) {
                $query->where('similarity_score', '>=', $this->minScore);
            });

        // Apply sorting
        if ($this->sortBy['column'] === 'job_post.title') {
            $query->join('job_posts', 'resumes.job_post_id', '=', 'job_posts.id')
                ->orderBy('job_posts.title', $this->sortBy['direction'])
                ->select('resumes.*');
        } elseif ($this->sortBy['column'] === 'job_seeker_detail.user.name') {
            $query->join('job_seeker_details', 'resumes.job_seeker_detail_id', '=', 'job_seeker_details.id')
                ->join('users', 'job_seeker_details.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->sortBy['direction'])
                ->select('resumes.*');
        } else {
            $query->orderBy($this->sortBy['column'], $this->sortBy['direction']);
        }

        return $query->paginate($this->perPage);
    }

    public function viewResume(Resume $resume)
    {
        try {
            $this->authorize('view', $resume);
            $this->selectedResume = $resume->load(['jobPost', 'jobSeekerDetail.user']);
            $this->viewingResume = true;
        } catch (\Exception $e) {
            Log::error('Error viewing resume: '.$e->getMessage());
            $this->error('Failed to load resume.');
        }
    }

    public function downloadResume(Resume $resume)
    {
        try {
            $this->authorize('download', $resume);
            $path = storage_path('app/public/'.$resume->file_path);

            if (! file_exists($path)) {
                throw new \Exception('Resume file not found');
            }

            return response()->download($path, $resume->file_name);
        } catch (\Exception $e) {
            Log::error('Error downloading resume: '.$e->getMessage());
            $this->error('Failed to download resume.');
        }
    }

    public function openStatusModal(Resume $resume)
    {
        $this->authorize('changeStatus', $resume);
        $this->selectedResume = $resume;
        $this->newStatus = $resume->application_status;
        $this->statusModal = true;
    }

    public function updateStatus()
    {
        try {
            $this->authorize('changeStatus', $this->selectedResume);

            $this->validate([
                'newStatus' => 'required|in:pending,reviewed,shortlisted,rejected',
            ]);

            $this->selectedResume->update([
                'application_status' => $this->newStatus,
            ]);

            $this->success('Application status updated successfully.');
            $this->statusModal = false;
            $this->selectedResume = null;

        } catch (\Exception $e) {
            Log::error('Error updating status: '.$e->getMessage());
            $this->error('Failed to update status.');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedJobPost = null;
        $this->statusFilter = 'all';
        $this->minScore = 0.0;
        $this->resetPage();
    }

    public function getScoreColorClass($score): string
    {
        if ($score === null) {
            return 'text-gray-400';
        }

        return match (true) {
            $score >= 0.8 => 'text-green-600 font-bold',
            $score >= 0.6 => 'text-blue-600 font-semibold',
            $score >= 0.4 => 'text-yellow-600',
            default => 'text-red-600'
        };
    }

    public function render()
    {
        return view('livewire.hr.view-applications', [
            'applications' => $this->applications(),
            'headers' => $this->headers(),
            'jobPosts' => $this->getJobPostsProperty(),
        ]);
    }
}
