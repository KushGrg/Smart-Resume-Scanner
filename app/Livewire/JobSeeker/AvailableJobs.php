<?php

namespace App\Livewire\JobSeeker;

use App\Exceptions\TextExtractionException;
use App\Models\Hr\JobPost;
use App\Services\JobQueryService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

/**
 * Available Jobs component for job seekers
 *
 * Handles viewing available job posts and submitting resume applications.
 * Implements proper error handling and user feedback for file uploads.
 */
class AvailableJobs extends Component
{
    use Toast, WithFileUploads, WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public $selectedJob = null;

    public bool $viewingJob = false;

    public bool $applyingJob = false;

    public $resume;

    protected JobQueryService $jobQueryService;

    public function boot(JobQueryService $jobQueryService)
    {
        $this->jobQueryService = $jobQueryService;
    }

    /**
     * Get available job posts with search functionality
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function availableJobs()
    {
        try {
            return $this->jobQueryService->getJobPostsWithResumes(
                search: $this->search,
                perPage: $this->perPage,
                status: 'active'
            );
        } catch (\Exception $e) {
            Log::error('Failed to fetch available jobs', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'search_term' => $this->search,
            ]);

            $this->toast(
                type: 'error',
                title: 'Error Loading Jobs',
                description: 'Unable to load job listings. Please try again.',
            );

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    /**
     * View detailed information for a specific job
     *
     * @param  int  $id  Job post ID
     * @return void
     */
    public function viewJob($id)
    {
        try {
            $this->selectedJob = JobPost::findOrFail($id);
            $this->viewingJob = true;
            $this->applyingJob = false;
        } catch (\Exception $e) {
            Log::error('Failed to view job details', [
                'job_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->toast(
                type: 'error',
                title: 'Job Not Found',
                description: 'The requested job could not be found.',
            );
        }
    }

    /**
     * Initialize job application process
     *
     * @param  int  $id  Job post ID
     * @return void
     */
    public function applyJob($id)
    {
        try {
            $this->selectedJob = JobPost::findOrFail($id);
            $this->applyingJob = true;
            $this->viewingJob = false;
        } catch (\Exception $e) {
            Log::error('Failed to initiate job application', [
                'job_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->toast(
                type: 'error',
                title: 'Application Error',
                description: 'Unable to start application process. Please try again.',
            );
        }
    }

    /**
     * Submit job application with resume
     *
     * @return void
     */
    public function submitApplication()
    {
        try {
            // Validate user authentication and job seeker profile
            if (! auth()->user() || ! auth()->user()->jobSeekerDetail) {
                throw new \Exception('Job seeker profile not found');
            }

            // Validate resume file
            $this->validate([
                'resume' => 'mimes:pdf,doc,docx|max:2048',
            ], [
                'resume.mimes' => 'The resume must be a file of type: pdf, doc, docx.',
                'resume.max' => 'The resume may not be greater than 2MB in size.',
            ]);

            // Store resume file
            $path = $this->resume->store('resumes', 'public');

            if (! $path) {
                throw new TextExtractionException('Failed to store resume file');
            }

            // Save to resumes table
            auth()->user()->jobSeekerDetail->resumes()->create([
                'job_seeker_detail_id' => auth()->user()->jobSeekerDetail->id,
                'job_post_id' => $this->selectedJob->id,
                'file_path' => $path,
            ]);

            Log::info('Job application submitted successfully', [
                'job_id' => $this->selectedJob->id,
                'user_id' => auth()->id(),
                'file_path' => $path,
            ]);

            $this->toast(
                type: 'success',
                title: 'Application Submitted',
                description: 'Your application has been submitted successfully.',
            );

            $this->reset(['applyingJob', 'resume']);

        } catch (TextExtractionException $e) {
            Log::error('Resume processing failed during application', [
                'job_id' => $this->selectedJob?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            $this->toast(
                type: 'error',
                title: 'Resume Processing Failed',
                description: 'There was an issue processing your resume. Please try again.',
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors naturally
            throw $e;
        } catch (\Exception $e) {
            Log::error('Application submission failed', [
                'job_id' => $this->selectedJob?->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toast(
                type: 'error',
                title: 'Application Failed',
                description: 'Unable to submit your application. Please try again later.',
            );
        }
    }

    public function render()
    {
        return view('livewire.job-seeker.available-jobs', [
            'jobs' => $this->availableJobs(),
        ]);
    }
}
