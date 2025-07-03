<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Model;

class JobPost extends Model
{
    protected $table = "job_posts";
    protected $fillable = [
        'hid',
        'title',
        'description',
        'location',
        'type',
        'deadline',
        'requirement',
        'experience',
        'status'
    ];
}
