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
            // dd($this->selectedJob->requirements);
            // dd($this->availableJobs()->requirements->get());
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
    // public function submitApplication()
    // {
    //     try {
    //         // ✅ Validate user authentication and job seeker profile
    //         $jobSeekerDetail = auth()->user()?->jobSeekerDetail;

    //         if (!auth()->user() || !$jobSeekerDetail) {
    //             throw new \Exception('Job seeker profile not found.');
    //         }

    //         // ✅ Validate resume file
    //         $this->validate([
    //             'resume' => 'mimes:pdf,doc,docx|max:2048',
    //         ], [
    //             'resume.mimes' => 'The resume must be a file of type: pdf, doc, docx.',
    //             'resume.max' => 'The resume may not be greater than 2MB in size.',
    //         ]);

    //         // ✅ Store resume file
    //         $path = $this->resume->store('resumes', 'public');

    //         if (!$path) {
    //             throw new \Exception('Failed to store resume file.');
    //         }

    //         // ✅ Insert or update the resume for this job
    //         $jobSeekerDetail->resumes()->updateOrCreate(
    //             [
    //                 'job_seeker_detail_id' => $jobSeekerDetail->id,
    //                 'job_post_id' => $this->selectedJob->id,
    //             ],
    //             [
    //                 'file_path' => $path,
    //                 'file_name' => $this->resume->getClientOriginalName(),
    //                 'file_type' => $this->resume->getClientOriginalExtension(),
    //             ]
    //         );

    //         Log::info('Job application submitted (or updated) successfully', [
    //             'job_id' => $this->selectedJob->id,
    //             'user_id' => auth()->id(),
    //             'file_path' => $path,
    //         ]);

    //         // ✅ Success message
    //         $this->toast(
    //             type: 'success',
    //             title: 'Application Submitted',
    //             description: 'Your application has been submitted successfully.',
    //         );

    //         session()->flash('message', 'Application submitted successfully.');
    //         $this->reset(['applyingJob', 'resume']);

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         // Let Livewire handle validation errors
    //         throw $e;
    //     } catch (\Exception $e) {
    //         Log::error('Application submission failed', [
    //             'job_id' => $this->selectedJob?->id,
    //             'user_id' => auth()->id(),
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //         ]);

    //         $this->toast(
    //             type: 'error',
    //             title: 'Application Failed',
    //             description: 'Unable to submit your application. Please try again later.',
    //         );
    //     }
    // }
    public function submitApplication()
    {
        try {
            // ✅ Validate user authentication and job seeker profile
            $jobSeekerDetail = auth()->user()?->jobSeekerDetail;

            if (!auth()->user() || !$jobSeekerDetail) {
                throw new \Exception('Job seeker profile not found.');
            }

            // ✅ Validate resume file - ADD 'required' rule
            $this->validate([
                'resume' => 'required|mimes:pdf,doc,docx|max:2048',
            ], [
                'resume.required' => 'Please upload your resume.',
                'resume.mimes' => 'The resume must be a file of type: pdf, doc, docx.',
                'resume.max' => 'The resume may not be greater than 2MB in size.',
            ]);

            // ✅ Ensure file is a valid uploaded file
            if (!$this->resume instanceof \Illuminate\Http\UploadedFile) {
                throw new \Exception('Invalid file upload.');
            }

            // ✅ Store resume file
            $path = $this->resume->store('resumes', 'public');

            if (!$path) {
                throw new \Exception('Failed to store resume file.');
            }

            // ✅ Insert or update the resume for this job
            $jobSeekerDetail->resumes()->updateOrCreate(
                [
                    'job_seeker_detail_id' => $jobSeekerDetail->id,
                    'job_post_id' => $this->selectedJob->id,
                ],
                [
                    'file_path' => $path,
                    'file_name' => $this->resume->getClientOriginalName(),
                    'file_type' => $this->resume->getClientOriginalExtension(),
                ]
            );

            Log::info('Job application submitted successfully', [
                'job_id' => $this->selectedJob->id,
                'user_id' => auth()->id(),
                'file_path' => $path,
            ]);

            // ✅ Success message
            $this->toast(
                type: 'success',
                title: 'Application Submitted',
                description: 'Your application has been submitted successfully.',
            );

            // Close modal and reset
            $this->reset(['applyingJob', 'resume', 'selectedJob']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors
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
                description: $e->getMessage(),
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
