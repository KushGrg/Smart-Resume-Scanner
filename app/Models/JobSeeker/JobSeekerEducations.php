<?php

namespace App\Models\JobSeeker;

use Illuminate\Database\Eloquent\Model;

class JobSeekerEducations extends Model
{
    //
    protected $fillable = [
        'job_seeker_info_id', 'school_name', 'location',
        'degree', 'field_of_study', 'start_date', 'end_date', 'description',
    ];

    public function jobSeekerInfo()
    {
        return $this->belongsTo(JobSeekerInfo::class, 'job_seeker_info_id');
    }
}
