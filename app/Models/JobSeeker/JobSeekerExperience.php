<?php

namespace App\Models\JobSeeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerExperience extends Model
{
    use HasFactory;

    protected $table = 'job_seeker_experiences';

    protected $fillable = [
        'job_seeker_id',
        'job_title',
        'employer',
        'location',
        'start_date',
        'end_date',
        'work_summary',
    ];

    protected $casts = [
        'job_seeker_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the job seeker (user) that owns this experience.
     */
    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
