<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

trait HasAdvancedScopes
{
    /**
     * Scope a query to records created within a specific time range.
     */
    public function scopeTimeRange(Builder $query, string $range): Builder
    {
        return match ($range) {
            'today' => $query->whereDate('created_at', today()),
            'yesterday' => $query->whereDate('created_at', now()->subDay()->toDateString()),
            'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'last_week' => $query->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]),
            'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'last_month' => $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
            'this_year' => $query->whereYear('created_at', now()->year),
            'last_year' => $query->whereYear('created_at', now()->subYear()->year),
            default => $query
        };
    }

    /**
     * Scope a query to recent records.
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to old records.
     */
    public function scopeOld(Builder $query, int $days = 365): Builder
    {
        return $query->where('created_at', '<=', now()->subDays($days));
    }

    /**
     * Scope a query to active records (for models with status field).
     */
    public function scopeActive(Builder $query): Builder
    {
        if (in_array('status', $this->getFillable())) {
            return $query->where('status', 'active');
        }

        return $query;
    }

    /**
     * Scope a query to inactive records (for models with status field).
     */
    public function scopeInactive(Builder $query): Builder
    {
        if (in_array('status', $this->getFillable())) {
            return $query->where('status', 'inactive');
        }

        return $query;
    }

    /**
     * Scope a query to records with a specific status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        if (in_array('status', $this->getFillable())) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Scope a query for pagination (returns query builder for chaining).
     */
    public function scopePaginated(Builder $query, int $perPage = 15): Builder
    {
        return $query->latest();
    }

    /**
     * Scope a query to include only verified records (for models with email_verified_at).
     */
    public function scopeVerified(Builder $query): Builder
    {
        if (in_array('email_verified_at', $this->getFillable()) ||
            in_array('verified_at', $this->getFillable())) {
            return $query->whereNotNull($this->getTable().'.email_verified_at')
                ->orWhereNotNull($this->getTable().'.verified_at');
        }

        return $query;
    }

    /**
     * Scope a query to include only unverified records.
     */
    public function scopeUnverified(Builder $query): Builder
    {
        if (in_array('email_verified_at', $this->getFillable()) ||
            in_array('verified_at', $this->getFillable())) {
            return $query->whereNull($this->getTable().'.email_verified_at')
                ->whereNull($this->getTable().'.verified_at');
        }

        return $query;
    }

    /**
     * Scope a query to include soft deleted records if the model uses SoftDeletes.
     */
    public function scopeWithTrashed(Builder $query): Builder
    {
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            return $query->withTrashed();
        }

        return $query;
    }

    /**
     * Scope a query to include only soft deleted records if the model uses SoftDeletes.
     */
    public function scopeOnlyTrashed(Builder $query): Builder
    {
        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            return $query->onlyTrashed();
        }

        return $query;
    }

    /**
     * Get random records.
     */
    public function scopeRandom(Builder $query, int $limit = 5): Builder
    {
        return $query->inRandomOrder()->limit($limit);
    }

    /**
     * Scope a query to popular records (for models with view count or similar).
     */
    public function scopePopular(Builder $query, string $field = 'views_count'): Builder
    {
        if (in_array($field, $this->getFillable())) {
            return $query->orderBy($field, 'desc');
        }

        return $query;
    }
}
