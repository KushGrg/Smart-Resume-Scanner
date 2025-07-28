<?php

namespace App\Livewire\JobSeeker;

use Livewire\Component;
use App\Models\Hr\JobPost;
use Livewire\WithPagination;

class JobPostList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.job-seeker.job-post-list', [
            'jobs' => JobPost::where('deadline', '>=', now())
                ->orderBy('created_at', 'desc')
                ->paginate(10)
        ]);
    }
}
