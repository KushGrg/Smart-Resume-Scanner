<?php

namespace App\Models\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::log(
                'created',
                $model,
                [],
                $model->getAttributes(),
                'low',
                get_class($model).' created'
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            // Remove sensitive fields from logging
            $sensitiveFields = ['password', 'remember_token', 'email_verified_at'];
            foreach ($sensitiveFields as $field) {
                unset($changes[$field], $original[$field]);
            }

            if (! empty($changes)) {
                AuditLog::log(
                    'updated',
                    $model,
                    array_intersect_key($original, $changes),
                    $changes,
                    'medium',
                    get_class($model).' updated'
                );
            }
        });

        static::deleted(function ($model) {
            AuditLog::log(
                'deleted',
                $model,
                $model->getAttributes(),
                [],
                'high',
                get_class($model).' deleted'
            );
        });
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Log a custom action.
     */
    public function logAction(string $action, array $data = [], string $riskLevel = 'low', ?string $description = null): AuditLog
    {
        return AuditLog::log(
            $action,
            $this,
            [],
            $data,
            $riskLevel,
            $description ?: get_class($this)." {$action}"
        );
    }
}
