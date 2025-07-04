<?php

namespace App\Models\Job_seeker;

use App\Livewire\Hr\JobPost;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table = "resumes";
    protected $fillable = [
        'jsid',
        'jpostid',
        'resume_path',
        'status'
    ];
    public function job()
    {
        return $this->belongsTo(JobPost::class, 'jpostid');
    }

    public function jobSeeker()
    {
        return $this->belongsTo(Job_Seeker_details::class);
    }
}
