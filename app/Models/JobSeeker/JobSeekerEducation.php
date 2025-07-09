<?php

namespace App\Models\JobSeeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerEducation extends Model
{
    use HasFactory;

    protected $table = 'job_seeker_educations';

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

    protected $casts = [
        'job_seeker_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the job seeker (user) that owns this education.
     */
    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
