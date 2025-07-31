<?php

namespace App\Livewire\JobSeeker;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewCreatedResume extends Component
{
    use Toast;

    public $search = '';

    public $showTrashed = false;

    public bool $showResumeModal = false;

    public $selectedResume = null;

    protected $queryString = ['search', 'showTrashed'];

    public function getResumesProperty()
    {
        $query = \App\Models\JobSeeker\JobSeekerInfo::query()
            ->where('job_seeker_id', Auth::id());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('designation', 'like', '%' . $this->search . '%')
                    ->orWhere('summary', 'like', '%' . $this->search . '%');
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

    public function forceDeleteResume($id)
    {
        $resume = \App\Models\JobSeeker\JobSeekerInfo::onlyTrashed()
            ->where('job_seeker_id', Auth::id())
            ->findOrFail($id);

        // Delete the PDF file if exists
        if ($resume->pdf_path) {
            \Storage::delete('public/' . $resume->pdf_path);
        }

        $resume->forceDelete();
        $this->success('Resume permanently deleted.');
    }

    public function copyShareLink($id)
    {
        $publicUrl = route('public.resume.view', $id);
        $this->dispatch('copyToClipboard', $publicUrl);
        $this->success('Public link copied!');
    }

    public function showResumeDetails($id)
    {
        $this->selectedResume = \App\Models\JobSeeker\JobSeekerInfo::where('job_seeker_id', Auth::id())
            ->when($this->showTrashed, fn($q) => $q->onlyTrashed())
            ->findOrFail($id);

        $this->showResumeModal = true;
    }

    public function closeResumeModal()
    {
        $this->showResumeModal = false;
        $this->selectedResume = null;
    }

    public function downloadResume($id): ?StreamedResponse
    {
        try {
            $resume = \App\Models\JobSeeker\JobSeekerInfo::where('job_seeker_id', Auth::id())
                ->when($this->showTrashed, fn($q) => $q->onlyTrashed())
                ->findOrFail($id);

            if (!$resume->pdf_path) {
                $this->error('No PDF file associated with this resume');
                return null;
            }

            $filePath = storage_path('app/public/' . ltrim($resume->pdf_path, '/'));

            if (!file_exists($filePath)) {
                $this->error('PDF file not found in storage');
                return null;
            }

            return response()->download($filePath, basename($resume->pdf_path));
        } catch (\Exception $e) {
            $this->error('Failed to download resume: ' . $e->getMessage());
            return null;
        }
    }

    public function render()
    {
        return view('livewire.jobseeker.view-created-resume', [
            'resumes' => $this->resumes,
        ]);
    }
}
