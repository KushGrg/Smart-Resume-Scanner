<?php

namespace App\Models\JobSeeker;

// use App\Livewire\Hr\JobPost;
use App\Models\Hr\JobPost;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    //
    protected $table = 'resumes';

    protected $fillable = [
        'job_seeker_detail_id',
        'job_post_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'extracted_text',
        'text_extracted',
        'processed',
        'processed_at',
        'similarity_score',
        'applied_at',
        'application_status',
    ];

    protected $casts = [
        'text_extracted' => 'boolean',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'applied_at' => 'datetime',
        'similarity_score' => 'decimal:4',
    ];

    public function jobSeeker()
    {
        return $this->belongsTo(JobSeekerDetails::class, 'job_seeker_detail_id', 'id');
    }

    public function jobPost()
    {
        return $this->belongsTo(JobPost::class, 'job_post_id', 'id');
    }
}
