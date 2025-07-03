<?php

namespace App\Models\Job_seeker;

use Illuminate\Database\Eloquent\Model;

class Job_seeker_details extends Model
{
    //
    protected $table = "job_seeker_details";
    protected $fillable = [
        'jid',
        'name',
        'email',
        'phone',
        'designation'
    ];

    
}
