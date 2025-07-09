<?php

namespace App\Models\Job_seeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class JobSeekerExperiences extends Model
{
    //
    public $table = 'job_seeker_experiences';

    protected $fillable = [
        'job_seeker_id',
        'job_title',
        'employer',
        'location',
        'start_date',
        'end_date',
        'work_summary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
