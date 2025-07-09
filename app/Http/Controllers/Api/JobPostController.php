<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostRequest;
use App\Models\Hr\JobPost;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobPostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of job posts.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', JobPost::class);

        $query = JobPost::with(['user', 'resumes'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            })
            ->when($request->experience_level, function ($q, $level) {
                $q->where('experience_level', $level);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            });

        // Filter by user permissions
        if (auth()->user()->hasRole('hr')) {
            $query->where('user_id', auth()->id());
        } elseif (auth()->user()->hasRole('job_seeker')) {
            $query->where('status', 'active');
        }

        $jobPosts = $query->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $jobPosts,
            'message' => 'Job posts retrieved successfully',
        ]);
    }

    /**
     * Store a newly created job post.
     */
    public function store(JobPostRequest $request): JsonResponse
    {
        $jobPost = JobPost::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $jobPost->load('user'),
            'message' => 'Job post created successfully',
        ], 201);
    }

    /**
     * Display the specified job post.
     */
    public function show(JobPost $jobPost): JsonResponse
    {
        $this->authorize('view', $jobPost);

        $jobPost->load(['user', 'resumes.jobSeekerDetail']);

        return response()->json([
            'status' => 'success',
            'data' => $jobPost,
            'message' => 'Job post retrieved successfully',
        ]);
    }

    /**
     * Update the specified job post.
     */
    public function update(JobPostRequest $request, JobPost $jobPost): JsonResponse
    {
        $this->authorize('update', $jobPost);

        $jobPost->update($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => $jobPost->load('user'),
            'message' => 'Job post updated successfully',
        ]);
    }

    /**
     * Remove the specified job post.
     */
    public function destroy(JobPost $jobPost): JsonResponse
    {
        $this->authorize('delete', $jobPost);

        $jobPost->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Job post deleted successfully',
        ]);
    }

    /**
     * Get applications for a job post.
     */
    public function applications(JobPost $jobPost): JsonResponse
    {
        $this->authorize('viewApplications', $jobPost);

        $applications = $jobPost->resumes()
            ->with(['jobSeekerDetail'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $applications,
            'message' => 'Applications retrieved successfully',
        ]);
    }

    /**
     * Change job post status.
     */
    public function changeStatus(Request $request, JobPost $jobPost): JsonResponse
    {
        $this->authorize('changeStatus', $jobPost);

        $request->validate([
            'status' => 'required|in:draft,active,closed',
        ]);

        $jobPost->update(['status' => $request->status]);

        return response()->json([
            'status' => 'success',
            'data' => $jobPost,
            'message' => 'Job post status updated successfully',
        ]);
    }
}
