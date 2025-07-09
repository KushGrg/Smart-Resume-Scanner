<?php

namespace App\Models\Job_seeker;

use App\Models\User;
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
        'summary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
