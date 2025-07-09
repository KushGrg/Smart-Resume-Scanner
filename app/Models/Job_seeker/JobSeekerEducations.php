<?php

namespace App\Models\Job_seeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class JobSeekerEducations extends Model
{
    //
    protected $fillable = [
        'job_seeker_id',
        'school_name',
        'location',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
