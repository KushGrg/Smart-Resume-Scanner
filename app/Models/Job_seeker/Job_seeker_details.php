<?php

namespace App\Models\Job_seeker;

use Illuminate\Database\Eloquent\Model;

class Job_seeker_details extends Model
{
    protected $table = "job_seeker_details";
    protected $fillable = [
        'jid',
        'name',
        'email',
        'phone',
        'designation'
    ];

    public function jobSeekerDetail()
    {
        return $this->hasOne(Job_Seeker_details::class, 'id'); // adjust foreign key if needed
    }

    public function resumes()
    {
        return $this->hasMany(Resume::class, 'id');
    }
}
