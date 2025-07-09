<?php

namespace App\Models\JobSeeker;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Job Seeker Details Model
 *
 * Represents detailed information for job seekers including personal information,
 * contact details, and professional designation. This model stores extended
 * profile data beyond the basic User model.
 *
 * @property int $id Primary key
 * @property int $jid Foreign key to users table (job seeker ID)
 * @property string $name Full name of the job seeker
 * @property string $email Email address
 * @property string $phone Phone number
 * @property string $designation Professional designation/title
 * @property \Illuminate\Support\Carbon $created_at Creation timestamp
 * @property \Illuminate\Support\Carbon $updated_at Last update timestamp
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Resume> $resumes
 *
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails query()
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails whereJid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JobSeekerDetails whereDesignation($value)
 *
 * @since 1.0.0
 */
class JobSeekerDetails extends Model
{
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
        'jid',
        'name',
        'email',
        'phone',
        'designation',
    ];

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
        return $this->hasMany(Resume::class, 'jsid', 'id');
    }
}
