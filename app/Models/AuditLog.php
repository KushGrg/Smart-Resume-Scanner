<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'risk_level',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model.
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scope for high-risk activities.
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }

    /**
     * Scope for security-related activities.
     */
    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('action', [
            'login', 'logout', 'failed_login', 'password_reset',
            'email_verification', 'role_assigned', 'permission_granted',
            'account_locked', 'suspicious_activity',
        ]);
    }

    /**
     * Scope for user activities.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Create an audit log entry.
     */
    public static function log(
        string $action,
        $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        string $riskLevel = 'low',
        ?string $description = null
    ): self {
        $user = auth()->user();
        $request = request();

        return self::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'risk_level' => $riskLevel,
            'description' => $description,
        ]);
    }

    /**
     * Log security event.
     */
    public static function logSecurity(string $action, string $description, string $riskLevel = 'medium'): self
    {
        return self::log($action, null, [], [], $riskLevel, $description);
    }

    /**
     * Log failed login attempt.
     */
    public static function logFailedLogin(string $email, string $reason = 'Invalid credentials'): self
    {
        return self::log(
            'failed_login',
            null,
            [],
            ['email' => $email, 'reason' => $reason],
            'medium',
            "Failed login attempt for email: {$email}"
        );
    }

    /**
     * Log successful login.
     */
    public static function logSuccessfulLogin(User $user): self
    {
        return self::log(
            'login',
            $user,
            [],
            ['email' => $user->email],
            'low',
            "User {$user->name} logged in successfully"
        );
    }

    /**
     * Log role assignment.
     */
    public static function logRoleAssignment(User $user, array $oldRoles, array $newRoles): self
    {
        return self::log(
            'role_assigned',
            $user,
            ['roles' => $oldRoles],
            ['roles' => $newRoles],
            'high',
            "Role assignment changed for user {$user->name}"
        );
    }

    /**
     * Log permission changes.
     */
    public static function logPermissionChange($model, string $permission, string $action): self
    {
        return self::log(
            'permission_'.$action,
            $model,
            [],
            ['permission' => $permission],
            'high',
            "Permission {$permission} {$action} for ".get_class($model)
        );
    }
}
