<?php

namespace App\Models\JobSeeker;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSeekerInfo extends Model
{
    use HasFactory;

    protected $table = 'job_seeker_infos';

    protected $fillable = [
        'job_seeker_id',
        'name',
        'designation',
        'phone',
        'email',
        'country',
        'city',
        'address',
        'summary',
    ];

    protected $casts = [
        'job_seeker_id' => 'integer',
    ];

    /**
     * Get the job seeker (user) that owns this info.
     */
    public function jobSeeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'job_seeker_id');
    }
}
