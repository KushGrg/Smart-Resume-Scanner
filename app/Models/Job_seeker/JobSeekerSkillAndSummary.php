<?php

namespace App\Models\Job_seeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class JobSeekerSkillAndSummary extends Model
{
    //
    protected $fillable = ['job_seeker_id', 'skills', 'summary'];

    public function user()
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
