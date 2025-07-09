<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    /**
     * Scope a query to search across specified fields.
     */
    public function scopeSearch(Builder $query, string $search, ?array $fields = null): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $fields = $fields ?? $this->getSearchableFields();

        return $query->where(function ($q) use ($search, $fields) {
            foreach ($fields as $field) {
                if (str_contains($field, '.')) {
                    // Handle relationship fields
                    [$relation, $relationField] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($relationField, $search) {
                        $relationQuery->where($relationField, 'like', "%{$search}%");
                    });
                } else {
                    // Handle direct fields
                    if ($this->isJsonField($field)) {
                        // Handle JSON fields
                        $q->orWhereJsonContains($field, $search);
                    } else {
                        // Handle regular fields
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            }
        });
    }

    /**
     * Get the searchable fields for the model.
     */
    public function getSearchableFields(): array
    {
        return property_exists($this, 'searchable') ? $this->searchable : [];
    }

    /**
     * Check if a field is a JSON field.
     */
    protected function isJsonField(string $field): bool
    {
        $jsonFields = property_exists($this, 'jsonFields') ? $this->jsonFields : [];

        return in_array($field, $jsonFields);
    }

    /**
     * Scope a query to filter by multiple criteria.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (! empty($value)) {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Scope a query to sort by multiple fields.
     */
    public function scopeSort(Builder $query, array $sorts): Builder
    {
        foreach ($sorts as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $query;
    }
}
