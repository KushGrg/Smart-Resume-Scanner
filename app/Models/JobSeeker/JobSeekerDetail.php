<?php

namespace App\Models\JobSeeker;

use App\Models\Traits\HasAdvancedScopes;
use App\Models\Traits\Searchable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Job Seeker Detail Model
 *
 * Represents detailed information for job seekers including personal information,
 * contact details, professional designation, skills, and experience.
 *
 * @property int $id Primary key
 * @property int $user_id Foreign key to users table
 * @property string $name Full name of the job seeker
 * @property string $email Email address
 * @property string $phone Phone number
 * @property string $current_designation Professional designation/title
 * @property string $experience_years Years of experience
 * @property array $skills Skills array stored as JSON
 * @property string $summary Professional summary
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Resume> $resumes
 *
 * @since 1.0.0
 */
class JobSeekerDetail extends Model
{
    use HasAdvancedScopes, HasFactory, Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_seeker_details';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'current_designation',
        'experience_years',
        'skills',
        'summary',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'skills' => 'array', // Store as JSON
    ];

    /**
     * Define searchable fields for the Searchable trait.
     *
     * @var array
     */
    protected $searchable = [
        'name',
        'email',
        'current_designation',
        'summary',
        'skills',
    ];

    /**
     * Define JSON fields for the Searchable trait.
     *
     * @var array
     */
    protected $jsonFields = [
        'skills',
    ];

    /**
     * Get the user that owns this job seeker detail.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all resumes submitted by this job seeker.
     *
     * Retrieves all resume records associated with this job seeker,
     * including their applications to various job posts and similarity scores.
     *
     * @return HasMany<Resume>
     */
    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class, 'job_seeker_detail_id');
    }

    // Scopes
    public function scopeByExperience($query, string $experience)
    {
        return $query->where('experience_years', $experience);
    }

    public function scopeWithSkill($query, string $skill)
    {
        return $query->whereJsonContains('skills', $skill);
    }

    public function scopeByDesignation($query, string $designation)
    {
        return $query->where('current_designation', 'like', "%{$designation}%");
    }

    public function scopeExperienceRange($query, string $minExp, string $maxExp)
    {
        $experienceLevels = ['0-1', '1-3', '3-5', '5-10', '10+'];

        $minIndex = array_search($minExp, $experienceLevels);
        $maxIndex = array_search($maxExp, $experienceLevels);

        if ($minIndex !== false && $maxIndex !== false) {
            $rangeValues = array_slice($experienceLevels, $minIndex, $maxIndex - $minIndex + 1);

            return $query->whereIn('experience_years', $rangeValues);
        }

        return $query;
    }

    public function scopeHasResumes($query)
    {
        return $query->whereHas('resumes');
    }

    public function scopeActiveJobSeekers($query)
    {
        return $query->whereHas('resumes', function ($q) {
            $q->where('created_at', '>=', now()->subMonths(6));
        });
    }

    // Accessors
    public function getSkillsListAttribute(): string
    {
        return $this->skills ? implode(', ', $this->skills) : '';
    }

    public function getExperienceDisplayAttribute(): string
    {
        return match ($this->experience_years) {
            '0-1' => 'Less than 1 year',
            '1-3' => '1-3 years',
            '3-5' => '3-5 years',
            '5-10' => '5-10 years',
            '10+' => 'More than 10 years',
            default => $this->experience_years ?: 'Not specified'
        };
    }

    public function getTotalApplicationsAttribute(): int
    {
        return $this->resumes()->count();
    }

    public function getShortlistedApplicationsAttribute(): int
    {
        return $this->resumes()->where('application_status', 'shortlisted')->count();
    }

    public function getAverageScoreAttribute(): ?float
    {
        $avgScore = $this->resumes()->whereNotNull('similarity_score')->avg('similarity_score');

        return $avgScore ? round($avgScore, 2) : null;
    }

    public function hasSkill(string $skill): bool
    {
        return $this->skills && in_array($skill, $this->skills);
    }

    public function getProfileCompletenessAttribute(): int
    {
        $fields = ['name', 'email', 'phone', 'current_designation', 'experience_years', 'skills', 'summary'];
        $completed = 0;

        foreach ($fields as $field) {
            if ($this->$field) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }
}
