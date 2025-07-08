<?php

namespace App\Models\Job_seeker;

use Illuminate\Database\Eloquent\Model;

class JobSeekerInfo extends Model
{
    //
    protected $fillable = [
        'job_seeker_id',
        'name',
        'designation',
        'phone',
        'email',
        'country',
        'city',
        'address',
        'summary'
    ];

    public function experiences()
    {
        return $this->hasMany(JobSeekerExperiences::class);
    }

    public function educations()
    {
        return $this->hasMany(related: JobSeekerEducations::class);
    }

    public function skills()
    {
        return $this->hasMany(JobSeekerSkillAndSummary::class);
    }
}
