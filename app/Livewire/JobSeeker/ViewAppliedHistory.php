<?php

namespace App\Livewire\JobSeeker;

use App\Models\JobSeeker\Resume;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ViewAppliedHistory extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public $selectedResume = null;

    public bool $viewingResume = false;

    public function appliedJobs()
    {
        try {
            $user = auth()->user();

            if (! $user || ! $user->jobSeekerDetail) {
                return new LengthAwarePaginator([], 0, $this->perPage);
            }

            return $user->jobSeekerDetail->resumes()
                ->with('jobPost')
                ->when($this->search, function ($query) {
                    $query->whereHas('jobPost', function ($q) {
                        $q->where('title', 'like', '%'.$this->search.'%')
                            ->orWhere('description', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest()
                ->paginate($this->perPage);

        } catch (\Exception $e) {
            Log::error('Error fetching applied jobs: '.$e->getMessage());

            return new LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    public function viewResume($resumeId)
    {
        try {
            $this->selectedResume = Resume::findOrFail($resumeId);
            $this->viewingResume = true;
        } catch (\Exception $e) {
            Log::error('Error viewing resume: '.$e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to load resume.',
            ]);
        }
    }

    public function downloadResume($resumeId)
    {
        try {
            $resume = Resume::findOrFail($resumeId);
            $path = storage_path('app/public/'.$resume->file_path);

            if (! file_exists($path)) {
                throw new \Exception('Resume file not found');
            }

            return response()->download($path, basename($resume->file_path));
        } catch (\Exception $e) {
            Log::error('Error downloading resume: '.$e->getMessage());
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to download resume.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.job-seeker.view-applied-history', [
            'applications' => $this->appliedJobs(),
        ]);
    }
}
