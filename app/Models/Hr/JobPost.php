<?php

namespace App\Models\Hr;

use App\Models\JobSeeker\Resume;
use App\Models\Traits\HasAdvancedScopes;
use App\Models\Traits\Searchable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    use HasAdvancedScopes, HasFactory, Searchable;

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

    protected $casts = [
        'user_id' => 'integer',
        'deadline' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    protected $dates = ['deadline'];

    /**
     * Define searchable fields for the Searchable trait.
     *
     * @var array
     */
    protected $searchable = [
        'title',
        'description',
        'location',
        'requirements',
        'experience_level',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hrDetail(): BelongsTo
    {
        return $this->belongsTo(HrDetail::class, 'user_id', 'user_id');
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class, 'job_post_id');
    }

    // Scopes
    public function scopeActive($query): Builder
    {
        return $query->where('status', 'active')
            ->where('deadline', '>=', now());
    }

    public function scopeOpen($query): Builder
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', now());
            });
    }

    public function scopeByLocation($query, string $location): Builder
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeByType($query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByExperienceLevel($query, string $level): Builder
    {
        return $query->where('experience_level', $level);
    }

    public function scopeSearch($query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('requirements', 'like', "%{$search}%");
        });
    }

    public function scopeWithSalaryRange($query, ?float $minSalary = null, ?float $maxSalary = null): Builder
    {
        return $query->when($minSalary, function ($q, $min) {
            $q->where(function ($subQ) use ($min) {
                $subQ->where('salary_min', '>=', $min)
                    ->orWhere('salary_max', '>=', $min);
            });
        })->when($maxSalary, function ($q, $max) {
            $q->where(function ($subQ) use ($max) {
                $subQ->where('salary_max', '<=', $max)
                    ->orWhere('salary_min', '<=', $max);
            });
        });
    }

    public function scopeRecent($query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getSalaryRangeAttribute(): ?string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        if ($this->salary_min && $this->salary_max) {
            return 'NPR ' . number_format((float) $this->salary_min, 2) . ' - ' . number_format((float) $this->salary_max, 2);
        }

        return $this->salary_min ? 'NPR ' . number_format((float) $this->salary_min, 2) . '+' : 'Up to NPR ' . number_format((float) $this->salary_max, 2);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->deadline && $this->deadline < now();
    }

    public function getApplicationsCountAttribute(): int
    {
        return $this->resumes()->count();
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->deadline) {
            return null;
        }

        $diff = now()->diffInDays($this->deadline, false);

        return $diff > 0 ? $diff : 0;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'active' => $this->is_expired ? 'error' : 'success',
            'inactive' => 'warning',
            'closed' => 'neutral',
            default => 'neutral'
        };
    }
    public function getStatusAttribute($value)
    {
        if ($this->deadline && \Carbon\Carbon::parse($this->deadline)->isPast()) {
            return 'inactive';
        }

        return $value;
    }
}
