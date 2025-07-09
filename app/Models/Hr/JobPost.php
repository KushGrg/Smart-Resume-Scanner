<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    protected $table = 'job_posts';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'type',
        'deadline',
        'requirements',
        'experience_level',
        'status',
        'salary_min',
        'salary_max',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function resumes()
    {
        return $this->hasMany(\App\Models\JobSeeker\Resume::class, 'job_post_id', 'id');
    }
}
