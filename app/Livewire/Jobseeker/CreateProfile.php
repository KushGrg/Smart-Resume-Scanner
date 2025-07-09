<?php

namespace App\Livewire\Jobseeker;

use App\Models\Job_seeker\JobSeekerEducations;
use App\Models\Job_seeker\JobSeekerExperiences;
use App\Models\Job_seeker\JobSeekerInfo;
use App\Models\Job_seeker\JobSeekerSkillAndSummary;
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

    // Experience (array of items)
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

    // Education (array of items)
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

    // Step navigation
    public function next()
    {
        // dd("Hello");
        $this->validate($this->rules()[$this->step]);
        $this->step++;
    }

    public function back()
    {
        $this->step--;
    }

    public function addSkill()
    {
        if (! empty($this->newSkill)) {
            $this->skills[] = $this->newSkill;
            $this->newSkill = '';
        }
    }

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

    public function submit()
    {
        $this->validate(array_merge(...array_values($this->rules())));

        // Store main profile info
        $resume = JobSeekerInfo::create([
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
            $exp['job_seeker_id'] = Auth::id();
            JobSeekerExperiences::create($exp);
        }

        // Store educations
        foreach ($this->educations as $edu) {
            $edu['job_seeker_id'] = Auth::id();
            JobSeekerEducations::create($edu);
        }

        // Store skills and summary
        JobSeekerSkillAndSummary::create([
            'job_seeker_id' => Auth::id(),
            'skills' => json_encode($this->skills),
            'summary' => $this->summary,
        ]);

        // Generate PDF resume
        $pdfPath = $this->generateResumePdf($resume);

        // session()->flash('success', 'Resume created and PDF generated successfully!');
        // return redirect()->route('resume.preview', $resume->id);
        $filePath = $this->generateResumePdf($resume);

        return response()->download(storage_path('app/public/'.$filePath));
    }

    // Generate PDF using a Blade template and store it
    public function generateResumePdf($resume)
    {
        $data = [
            'resume' => $resume,
            'experiences' => $this->experiences,
            'educations' => $this->educations,
            'skills' => $this->skills,
            'summary' => $this->summary,
        ];
        $pdf = Pdf::loadView('pdf.resume-template', $data);
        $fileName = 'resume_'.$resume->id.'_'.time().'.pdf';
        $pdf->save(storage_path('app/public/resumes/'.$fileName));

        // Optionally, store the path in the DB
        // $resume->update(['pdf_path' => 'resumes/' . $fileName]);
        return 'resumes/'.$fileName;
    }

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
        return view('livewire.jobseeker.create-profile');
    }
}
