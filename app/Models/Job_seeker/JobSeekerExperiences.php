<?php

namespace App\Models\Job_seeker;

use Illuminate\Database\Eloquent\Model;

class JobSeekerExperiences extends Model
{
    //
    public $table = "experience";
    protected $fillable = [
        'job_seeker_info_id',
        'job_title',
        'employer',
        'location',
        'start_date',
        'end_date',
        'work_summary'
    ];

    public function jobSeekerInfo()
    {
        return $this->belongsTo(JobSeekerInfo::class, 'job_seeker_info_id');
    }
}
