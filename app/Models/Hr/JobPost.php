<?php

namespace App\Models\Hr;

use App\Models\Job_seeker\Resume;
use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    protected $table = "job_posts";
    protected $fillable = [
        'hid',
        'title',
        'description',
        'location',
        'type',
        'deadline',
        'requirement',
        'experience',
        'status'
    ];
    public function resumes()
    {
        return $this->hasMany(Resume::class, 'jpostid', 'id');
    }
}
