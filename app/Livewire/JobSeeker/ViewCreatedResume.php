<?php

namespace App\Livewire\JobSeeker;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

// use \App\Models\JobSeeker;

class ViewCreatedResume extends Component
{
    use Toast;

    public $search = '';

    public $showTrashed = false;

    protected $queryString = ['search', 'showTrashed'];

    public function getResumesProperty()
    {
        $query = \App\Models\JobSeeker\JobSeekerInfo::query()
            ->where('job_seeker_id', Auth::id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('designation', 'like', '%'.$this->search.'%')
                    ->orWhere('summary', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->showTrashed) {
            $query->onlyTrashed();
        }

        return $query->latest()->get();
    }

    public function emitShowResumeDetails($id)
    {
        $this->dispatch('showResumeDetails', $id);
    }

    public function deleteResume($id)
    {
        $resume = \App\Models\JobSeeker\JobSeekerInfo::where('job_seeker_id', Auth::id())->findOrFail($id);
        $resume->delete();
        $this->success('Resume moved to trash.');
    }

    public function restoreResume($id)
    {
        $resume = \App\Models\JobSeeker\JobSeekerInfo::onlyTrashed()->where('job_seeker_id', Auth::id())->findOrFail($id);
        $resume->restore();
        $this->success('Resume restored.');
    }

    public function downloadResume($id)
    {
        $resume = \App\Models\JobSeeker\JobSeekerInfo::where('  job_seeker_id', Auth::id())->findOrFail($id);
        if (! $resume->pdf_path) {
            $this->error('PDF not found.');

            return;
        }

        return response()->download(storage_path('app/public/'.$resume->pdf_path));
    }

    public function copyShareLink($id)
    {
        $publicUrl = route('public.resume.view', $id);
        $this->dispatch('copyToClipboard', $publicUrl);
        $this->success('Public link copied!');
    }

    public function render()
    {
        return view('livewire.jobseeker.view-created-resume', [
            'resumes' => $this->resumes,
        ]);
    }
}
