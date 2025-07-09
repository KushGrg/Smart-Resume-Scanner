<?php

namespace App\Models\JobSeeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerSkillAndSummary extends Model
{
    use HasFactory;

    protected $table = 'job_seeker_skill_and_summaries';

    protected $fillable = [
        'job_seeker_id',
        'skills',
        'summary',
    ];

    protected $casts = [
        'job_seeker_id' => 'integer',
    ];

    /**
     * Get the job seeker (user) that owns this skill and summary.
     */
    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }

    /**
     * Get skills as array (assuming JSON storage).
     */
    public function getSkillsArrayAttribute(): array
    {
        return is_string($this->skills) ? json_decode($this->skills, true) ?? [] : [];
    }

    /**
     * Set skills from array to JSON.
     */
    public function setSkillsAttribute($value): void
    {
        $this->attributes['skills'] = is_array($value) ? json_encode($value) : $value;
    }
}
