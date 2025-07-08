<?php

namespace App\Models\Job_seeker;

use Illuminate\Database\Eloquent\Model;

class JobSeekerSkillAndSummary extends Model
{
    //
    protected $fillable = ['job_seeker_info_id', 'name'];

    public function jobSeekerInfo()
    {
        return $this->belongsTo(JobSeekerInfo::class, 'job_seeker_info_id');
    }
}
