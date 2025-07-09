<?php

namespace App\Models;

use App\Models\Hr\HrDetail;
use App\Models\JobSeeker\JobSeekerDetail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'previously_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'previously_verified' => 'boolean',
    ];

    // Relationships
    public function hrDetail(): HasOne
    {
        return $this->hasOne(HrDetail::class, 'user_id');
    }

    public function jobSeekerDetail(): HasOne
    {
        return $this->hasOne(JobSeekerDetail::class, 'user_id');
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(\App\Models\Hr\JobPost::class, 'user_id');
    }

    // Role helper methods
    public function isHr(): bool
    {
        return $this->hasRole('hr');
    }

    public function isJobSeeker(): bool
    {
        return $this->hasRole('job_seeker');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super_admin']);
    }

    // Profile helper methods
    public function getProfileAttribute()
    {
        if ($this->isHr()) {
            return $this->hrDetail;
        }

        if ($this->isJobSeeker()) {
            return $this->jobSeekerDetail;
        }

        return null;
    }

    public function hasCompleteProfile(): bool
    {
        $profile = $this->profile;

        return $profile !== null && $profile->name && $profile->email && $profile->phone;
    }
}
