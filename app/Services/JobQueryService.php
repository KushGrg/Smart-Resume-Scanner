<?php

namespace App\Services;

use App\Models\Hr\JobPost;
use App\Models\JobSeeker\Resume;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Job Query Service
 *
 * Provides optimized database queries for job-related operations
 * with proper caching and performance optimization.
 */
class JobQueryService
{
    /**
     * Get job posts with optimized queries and caching
     *
     * @param  string  $search  Search term
     * @param  int  $perPage  Results per page
     * @param  string  $status  Job status filter
     */
    public function getJobPostsWithResumes(string $search = '', int $perPage = 10, string $status = 'active'): LengthAwarePaginator
    {
        return JobPost::query()
            ->with(['resumes' => function ($query) {
                $query->where('processed', true)
                    ->orderByDesc('similarity_score')
                    ->select(['id', 'job_seeker_detail_id', 'job_post_id', 'similarity_score', 'file_path']);
            }])
            ->where('status', $status)
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('requirements', 'like', "%{$search}%");
                });
            })
            ->select(['id', 'title', 'description', 'location', 'type', 'experience_level', 'created_at', 'deadline'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get user's applied jobs with optimized loading
     *
     * @param  int  $userId  User ID
     * @param  string  $search  Search term
     * @param  int  $perPage  Results per page
     */
    public function getUserAppliedJobs(int $userId, string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $cacheKey = "user_applied_jobs_{$userId}_".md5($search)."_{$perPage}";

        return Cache::remember($cacheKey, 120, function () use ($userId, $search, $perPage) {
            return Resume::query()
                ->with(['jobPost:id,title,description,location,type,status'])
                ->whereHas('jobSeekerDetail', function (Builder $query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->when($search, function (Builder $query) use ($search) {
                    $query->whereHas('jobPost', function (Builder $q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                })
                ->select(['id', 'job_seeker_detail_id', 'job_post_id', 'file_path', 'similarity_score', 'processed', 'created_at'])
                ->latest('created_at')
                ->paginate($perPage);
        });
    }

    /**
     * Get job post statistics with caching
     *
     * @param  int  $jobPostId  Job post ID
     */
    public function getJobPostStatistics(int $jobPostId): array
    {
        $cacheKey = "job_post_stats_{$jobPostId}";

        return Cache::remember($cacheKey, 600, function () use ($jobPostId) {
            $stats = DB::table('resumes')
                ->where('job_post_id', $jobPostId)
                ->where('processed', true)
                ->whereNotNull('similarity_score')
                ->selectRaw('
                    COUNT(*) as total_applications,
                    AVG(similarity_score) as avg_score,
                    MAX(similarity_score) as max_score,
                    MIN(similarity_score) as min_score,
                    COUNT(CASE WHEN similarity_score >= 0.7 THEN 1 END) as high_match_count,
                    COUNT(CASE WHEN similarity_score >= 0.4 AND similarity_score < 0.7 THEN 1 END) as medium_match_count,
                    COUNT(CASE WHEN similarity_score < 0.4 THEN 1 END) as low_match_count
                ')
                ->first();

            return [
                'total_applications' => $stats->total_applications ?? 0,
                'avg_score' => round($stats->avg_score ?? 0, 3),
                'max_score' => round($stats->max_score ?? 0, 3),
                'min_score' => round($stats->min_score ?? 0, 3),
                'high_match_count' => $stats->high_match_count ?? 0,
                'medium_match_count' => $stats->medium_match_count ?? 0,
                'low_match_count' => $stats->low_match_count ?? 0,
            ];
        });
    }

    /**
     * Clear cache for specific job post
     *
     * @param  int  $jobPostId  Job post ID
     */
    public function clearJobPostCache(int $jobPostId): void
    {
        Cache::forget("job_post_stats_{$jobPostId}");

        // Clear job listing caches (this is a simple approach, could be more sophisticated)
        $keys = ['active', 'inactive', 'draft'];
        foreach ($keys as $status) {
            Cache::tags(['job_posts', $status])->flush();
        }
    }

    /**
     * Clear user application cache
     *
     * @param  int  $userId  User ID
     */
    public function clearUserApplicationCache(int $userId): void
    {
        Cache::tags(['user_applications', "user_{$userId}"])->flush();
    }
}
