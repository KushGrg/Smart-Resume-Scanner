<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class ResumeUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('job_seeker');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume' => [
                'required',
                'file',
                File::types(['pdf', 'doc', 'docx'])
                    ->max(2 * 1024), // 2MB max
            ],
            'job_post_id' => ['required', 'exists:job_posts,id'],
            'cover_letter' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resume.required' => 'Resume file is required.',
            'resume.file' => 'Resume must be a valid file.',
            'resume.mimes' => 'Resume must be a PDF, DOC, or DOCX file.',
            'resume.max' => 'Resume file size must not exceed 2MB.',
            'job_post_id.required' => 'Job post selection is required.',
            'job_post_id.exists' => 'Selected job post does not exist.',
            'cover_letter.max' => 'Cover letter cannot exceed 2000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Additional security checks
            if ($this->hasFile('resume')) {
                $file = $this->file('resume');

                // Check MIME type
                $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                if (! in_array($file->getMimeType(), $allowedMimes)) {
                    $validator->errors()->add('resume', 'Invalid file type. Only PDF, DOC, and DOCX files are allowed.');
                }

                // Check file signature for additional security
                $fileContent = file_get_contents($file->getPathname());
                if ($fileContent === false) {
                    $validator->errors()->add('resume', 'Unable to read file contents.');

                    return;
                }

                // Basic file signature validation
                $signatures = [
                    'pdf' => ['%PDF'],
                    'doc' => ['\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1'], // Old DOC format
                    'docx' => ['PK\x03\x04'], // ZIP-based format
                ];

                $isValidSignature = false;
                foreach ($signatures as $type => $sigs) {
                    foreach ($sigs as $sig) {
                        if (strpos($fileContent, $sig) === 0) {
                            $isValidSignature = true;
                            break 2;
                        }
                    }
                }

                if (! $isValidSignature) {
                    $validator->errors()->add('resume', 'File appears to be corrupted or has an invalid format.');
                }
            }
        });
    }
}
