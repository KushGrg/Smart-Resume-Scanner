<?php

namespace App\Models\JobSeeker;

use App\Models\Hr\JobPost;
use App\Models\Traits\HasAdvancedScopes;
use App\Models\Traits\HasFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resume extends Model
{
    use HasAdvancedScopes, HasFactory, HasFileUploads;

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
        'job_seeker_detail_id' => 'integer',
        'job_post_id' => 'integer',
        'file_size' => 'integer',
        'text_extracted' => 'boolean',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'applied_at' => 'datetime',
        'similarity_score' => 'decimal:4',
    ];

    // Relationships
    public function jobSeekerDetail(): BelongsTo
    {
        return $this->belongsTo(JobSeekerDetail::class, 'job_seeker_detail_id');
    }

    public function jobSeeker(): BelongsTo
    {
        return $this->jobSeekerDetail();
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class, 'job_post_id');
    }

    // Scopes
    public function scopeProcessed($query): Builder
    {
        return $query->where('processed', true);
    }

    public function scopePending($query): Builder
    {
        return $query->where('processed', false);
    }

    public function scopeTextExtracted($query): Builder
    {
        return $query->where('text_extracted', true);
    }

    public function scopeByStatus($query, string $status): Builder
    {
        return $query->where('application_status', $status);
    }

    public function scopeHighScore($query, float $minScore = 0.7): Builder
    {
        return $query->where('similarity_score', '>=', $minScore);
    }

    public function scopeRankedByScore($query): Builder
    {
        return $query->orderBy('similarity_score', 'desc');
    }

    public function scopeRecent($query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByFileType($query, string $type): Builder
    {
        return $query->where('file_type', $type);
    }

    public function scopeForJobPost($query, int $jobPostId): Builder
    {
        return $query->where('job_post_id', $jobPostId);
    }

    public function scopeForJobSeeker($query, int $jobSeekerId): Builder
    {
        return $query->where('job_seeker_detail_id', $jobSeekerId);
    }

    // Accessors
    public function getFileUrlAttribute(): string
    {
        return asset("storage/{$this->file_path}");
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getScorePercentageAttribute(): ?string
    {
        return $this->similarity_score ? round($this->similarity_score * 100, 2) . '%' : null;
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->application_status) {
            'pending' => 'warning',
            'reviewed' => 'info',
            'shortlisted' => 'success',
            'rejected' => 'error',
            default => 'neutral'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match ($this->application_status) {
            'pending' => 'In Review',
            'reviewed' => 'Reviewed',
            'shortlisted' => 'Shortlisted',
            'rejected' => 'Not Selected',
            default => 'Unknown'
        };
    }

    public function getIsHighScoringAttribute(): bool
    {
        return $this->similarity_score && $this->similarity_score >= 0.7;
    }

    public function getDaysAgoAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    // Helper methods
    public function isProcessed(): bool
    {
        return $this->processed === true;
    }

    public function hasTextExtracted(): bool
    {
        return $this->text_extracted === true;
    }

    public function isPending(): bool
    {
        return $this->application_status === 'pending';
    }

    public function isShortlisted(): bool
    {
        return $this->application_status === 'shortlisted';
    }

    public function isRejected(): bool
    {
        return $this->application_status === 'rejected';
    }
}
