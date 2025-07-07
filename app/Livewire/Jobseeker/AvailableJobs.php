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
        // dd(auth()->user()?->jobSeekerDetail?->id);
        $this->validate([
            'resume' => 'mimes:pdf,doc,docx|max:2048'
        ], [
            'resume.mimes' => 'The resume must be a file of type: pdf, doc, docx.',
            'resume.max' => 'The resume may not be greater than 2MB in size.'
        ]);

        $path = $this->resume->store('resumes', 'public');

        // Save to resumes table
        auth()->user()->jobSeekerDetail->resumes()->create([
            'jsid' => auth()->user()->jobSeekerDetail->id,
            'jpostid' => $this->selectedJob->id,
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
