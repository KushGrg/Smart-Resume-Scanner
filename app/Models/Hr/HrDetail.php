<?php

namespace App\Models\Hr;

use App\Models\Traits\HasAdvancedScopes;
use App\Models\Traits\Searchable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrDetail extends Model
{
    use HasAdvancedScopes, HasFactory, Searchable;

    protected $table = 'hr_details';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'organization_name',
        'logo',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Define searchable fields for the Searchable trait.
     *
     * @var array
     */
    protected $searchable = [
        'name',
        'email',
        'organization_name',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class, 'user_id', 'user_id');
    }

    // Scopes
    public function scopeByOrganization($query, string $organization)
    {
        return $query->where('organization_name', 'like', "%{$organization}%");
    }

    public function scopeVerified($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->whereNotNull('email_verified_at');
        });
    }

    public function scopeWithActiveJobs($query)
    {
        return $query->whereHas('jobPosts', function ($q) {
            $q->where('status', 'active');
        });
    }

    // Accessors
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset("storage/{$this->logo}") : null;
    }

    public function getActiveJobsCountAttribute(): int
    {
        return $this->jobPosts()->where('status', 'active')->count();
    }

    public function getTotalApplicationsAttribute(): int
    {
        return $this->jobPosts()->withCount('resumes')->get()->sum('resumes_count');
    }
}
