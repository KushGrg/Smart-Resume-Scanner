<?php

namespace App\Livewire\Jobseeker;

use App\Models\Hr\JobPost;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class AvailableJobs extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public int $perPage = 10;

    public $selectedJob = null;
    public bool $viewingJob = false;
    public bool $applyingJob = false;
    public $resume;

    public function availableJobs()
    {
        return JobPost::query()
            ->where('status', 'active')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('location', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function viewJob($id)
    {
        $this->selectedJob = JobPost::findOrFail($id);
        $this->viewingJob = true;
        $this->applyingJob = false;
    }

    public function applyJob($id)
    {
        $this->selectedJob = JobPost::findOrFail($id);
        $this->applyingJob = true;
        $this->viewingJob = false;
    }

    public function submitApplication()
    {
        $this->validate([
            'resume' => 'required|mimes:pdf,doc,docx|max:2048'
        ]);

        $path = $this->resume->store('resumes', 'public');

        // Example: Save to database (JobApplication model assumed)
        auth()->user()->applications()->create([
            'job_post_id' => $this->selectedJob->id,
            'resume_path' => $path,
        ]);

        session()->flash('message', 'Application submitted successfully.');
        $this->reset(['applyingJob', 'resume']);
    }

    public function render()
    {
        return view('livewire.jobseeker.available-jobs', [
            'jobs' => $this->availableJobs()
        ]);
    }
}
