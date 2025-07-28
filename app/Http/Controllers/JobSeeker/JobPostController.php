<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\Hr\JobPost;
use App\Http\Controllers\Controller;

class JobPostController extends Controller
{
    public function show(JobPost $job)
    {
        if ($job->deadline < now()) {
            abort(403, 'This job posting has expired');
        }
        
        return view('job-seeker.jobs.show', compact('job'));
    }
}
