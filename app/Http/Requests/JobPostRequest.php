<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create job posts');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'location' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:full_time,part_time,contract,remote'],
            'experience_level' => ['required', 'in:entry,mid,senior,executive'],
            'requirements' => ['required', 'string', 'min:30'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'gt:salary_min'],
            'status' => ['nullable', 'in:draft,active,closed'],
            'urgency' => ['nullable', 'in:low,medium,high,urgent'],
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
            'title.required' => 'Job title is required.',
            'title.max' => 'Job title cannot exceed 255 characters.',
            'description.required' => 'Job description is required.',
            'description.min' => 'Job description must be at least 50 characters.',
            'location.required' => 'Job location is required.',
            'type.required' => 'Job type is required.',
            'type.in' => 'Job type must be one of: full_time, part_time, contract, remote.',
            'experience_level.required' => 'Experience level is required.',
            'experience_level.in' => 'Experience level must be one of: entry, mid, senior, executive.',
            'requirements.required' => 'Job requirements are required.',
            'requirements.min' => 'Job requirements must be at least 30 characters.',
            'deadline.after' => 'Application deadline must be in the future.',
            'salary_min.numeric' => 'Minimum salary must be a number.',
            'salary_min.min' => 'Minimum salary cannot be negative.',
            'salary_max.numeric' => 'Maximum salary must be a number.',
            'salary_max.gt' => 'Maximum salary must be greater than minimum salary.',
            'status.in' => 'Status must be one of: draft, active, closed.',
            'urgency.in' => 'Urgency must be one of: low, medium, high, urgent.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'salary_min' => 'minimum salary',
            'salary_max' => 'maximum salary',
            'experience_level' => 'experience level',
        ];
    }
}
