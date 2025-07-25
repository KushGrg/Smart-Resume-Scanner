<?php

namespace App\Livewire\JobSeeker;

use App\Models\JobSeeker\JobSeekerEducation;
use App\Models\JobSeeker\JobSeekerExperience;
use App\Models\JobSeeker\JobSeekerInfo;
use App\Models\JobSeeker\JobSeekerSkillAndSummary;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResumeDetailsModal extends Component
{
    public $show = false;

    public $resumeId;

    public $resume;

    public $experiences = [];

    public $educations = [];

    public $skills = [];

    public $summary = '';

    protected $listeners = ['showResumeDetails' => 'showModal'];

    public function showModal($id)
    {
        $this->resumeId = $id;
        $this->resume = JobSeekerInfo::where('job_seeker_id', Auth::id())->findOrFail($id);
        $this->experiences = JobSeekerExperience::where('job_seeker_id', Auth::id())->get()->toArray();
        $this->educations = JobSeekerEducation::where('job_seeker_id', Auth::id())->get()->toArray();
        $skillSummary = JobSeekerSkillAndSummary::where('job_seeker_id', Auth::id())->first();
        $this->skills = $skillSummary ? json_decode($skillSummary->skills, true) : [];
        $this->summary = $skillSummary ? $skillSummary->summary : '';
        $this->show = true;
    }

    public function closeModal()
    {
        $this->show = false;
    }

    public function render()
    {
        return view('livewire.jobseeker.resume-details-modal', [
            'resume' => $this->resume,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
            'skills' => $this->skills,
            'summary' => $this->summary,
        ]);
    }
}
