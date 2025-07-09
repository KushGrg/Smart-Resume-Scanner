<?php

namespace App\Livewire\JobSeeker;

use App\Models\JobSeeker\JobSeekerInfo;
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

    public function submit()
    {
        $this->validate(array_merge(...array_values($this->rules())));

        $resume = JobSeekerInfo::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            // 'last_name' => $this->last_name,
            'designation' => $this->designation,
            'phone' => $this->phone,
            'email' => $this->email,
            'country' => $this->country,
            'city' => $this->city,
            'address' => $this->address,
        ]);

        foreach ($this->experiences as $exp) {
            $resume->experiences()->create($exp);
        }

        foreach ($this->educations as $edu) {
            $resume->educations()->create($edu);
        }

        foreach ($this->skills as $skill) {
            $resume->skills()->create(['name' => $skill]);
        }

        session()->flash('success', 'Resume created successfully!');

        return redirect()->route('resume.preview', $resume->id);
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
        return view('livewire.job-seeker.create-profile');
    }
}
