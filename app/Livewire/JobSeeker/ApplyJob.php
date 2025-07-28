<?php

namespace App\Livewire\JobSeeker;

use Livewire\Component;
use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use Livewire\WithFileUploads;

class ApplyJob extends Component
{
    use WithFileUploads;

    public JobPost $job;
    public $resume;

    public function mount(JobPost $job)
    {
        if ($job->deadline < now()) {
            abort(403, 'This job posting has expired');
        }
        $this->job = $job;
    }

    // ...existing code...
}