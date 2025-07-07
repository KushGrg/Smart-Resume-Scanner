<?php

namespace App\Livewire\Jobseeker;

use App\Models\Hr\JobPost;
use Livewire\Component;
use Livewire\WithPagination;
class ViewAppliedHistory extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public function appliedJobs()
    {
        return auth()->user()->jobSeekerDetail->resumes()
            ->with('jobPost')
            ->when($this->search, function ($query) {
                $query->whereHas('jobPost', function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.jobseeker.view-applied-history', [
            'applications' => $this->appliedJobs()
        ]);
    }
}
