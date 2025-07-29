<?php

namespace App\Livewire\JobSeeker;

use App\Models\JobSeeker\JobSeekerEducation;
use App\Models\JobSeeker\JobSeekerExperience;
use App\Models\JobSeeker\JobSeekerInfo;
use App\Models\JobSeeker\JobSeekerSkillAndSummary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateProfile extends Component
{
    public $step = 1;

    // Profile Info
    public $name;

    public $designation;

    public $phone;

    public $email;

    public $country;

    public $city;

    public $address;

    public $summary;

    // Experience
    public $experiences = [
        [
            'job_title' => '',
            'employer' => '',
            'location' => '',
            'start_date' => '',
            'end_date' => '',
            'work_summary' => '',
        ],
    ];

    // Education
    public $educations = [
        [
            'school_name' => '',
            'location' => '',
            'degree' => '',
            'field_of_study' => '',
            'start_date' => '',
            'end_date' => '',
            'description' => '',
        ],
    ];

    // Skills
    public $skills = [];

    public $newSkill = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
    }

    // Navigation
    public function next()
    {
        $this->validate($this->rules()[$this->step]);
        $this->step++;
    }

    public function back()
    {
        $this->step--;
    }

    // Skills
    public function addSkill()
    {
        if (!empty($this->newSkill)) {
            $this->skills[] = $this->newSkill;
            $this->newSkill = '';
        }
    }

    public function removeSkill($index)
    {
        if (isset($this->skills[$index])) {
            unset($this->skills[$index]);
            $this->skills = array_values($this->skills); // Reindex array
        }
    }

    // Experience
    public function addExperience()
    {
        $this->experiences[] = [
            'job_title' => '',
            'employer' => '',
            'location' => '',
            'start_date' => '',
            'end_date' => '',
            'work_summary' => '',
        ];
    }

    public function removeExperience($index)
    {
        unset($this->experiences[$index]);
        $this->experiences = array_values($this->experiences);
    }

    // Education
    public function addEducation()
    {
        $this->educations[] = [
            'school_name' => '',
            'location' => '',
            'degree' => '',
            'field_of_study' => '',
            'start_date' => '',
            'end_date' => '',
            'description' => '',
        ];
    }

    public function removeEducation($index)
    {
        unset($this->educations[$index]);
        $this->educations = array_values($this->educations);
    }

    // Form Submission
    public function submit()
    {
        $this->validate(array_merge(...array_values($this->rules())));

        // Store profile
        $jobSeekerInfo = JobSeekerInfo::create([
            'job_seeker_id' => Auth::id(),
            'name' => $this->name,
            'designation' => $this->designation,
            'phone' => $this->phone,
            'email' => $this->email,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
            'summary' => $this->summary,
        ]);

        // Store experiences
        foreach ($this->experiences as $exp) {
            if (!empty($exp['job_title'])) {
                $exp['job_seeker_id'] = Auth::id();
                JobSeekerExperience::create($exp);
            }
        }

        // Store educations
        foreach ($this->educations as $edu) {
            if (!empty($edu['school_name'])) {
                $edu['job_seeker_id'] = Auth::id();
                JobSeekerEducation::create($edu);
            }
        }

        // Store skills and summary
        if (!empty($this->skills)) {
            JobSeekerSkillAndSummary::create([
                'job_seeker_id' => Auth::id(),
                'skills' => json_encode($this->skills),
                'summary' => $this->summary,
            ]);
        }

        // Generate PDF
        $filePath = $this->generateResumePdf($jobSeekerInfo);

        session()->flash('success', 'Resume created and PDF generated successfully!');

        return response()->download(storage_path('app/public/' . $filePath));
    }

    // Generate PDF
    public function generateResumePdf($jobSeekerInfo)
    {
        $pdfDirectory = storage_path('app/public/resumes');

        if (!file_exists($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }

        $data = [
            'jobSeekerInfo' => $jobSeekerInfo,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
            'skills' => $this->skills,
            'summary' => $this->summary,
        ];

        $pdf = Pdf::loadView('pdf.resume-template', $data);
        $fileName = 'resume_' . $jobSeekerInfo->id . '_' . time() . '.pdf';
        $pdf->save($pdfDirectory . '/' . $fileName);

        return 'resumes/' . $fileName;
    }

    // Validation Rules
    public function rules()
    {
        return [
            1 => [
                'name' => 'required|string|min:2',
                'designation' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|email',
                'country' => 'required|string',
                'city' => 'required|string',
                'address' => 'required|string',
            ],
            2 => [
                'experiences.*.job_title' => 'required|string',
                'experiences.*.employer' => 'required|string',
                'experiences.*.location' => 'required|string',
                'experiences.*.start_date' => 'required|date',
                'experiences.*.end_date' => 'nullable|date',
            ],
            3 => [
                'educations.*.school_name' => 'required|string',
                'educations.*.location' => 'required|string',
                'educations.*.degree' => 'required|string',
                'educations.*.field_of_study' => 'required|string',
                'educations.*.start_date' => 'required|date',
                'educations.*.end_date' => 'nullable|date',
            ],
            4 => [
                'summary' => 'required|string|min:10',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.job-seeker.create-profile');
    }
}
