<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('POST')) {
            return $this->user()->can('create users');
        }

        return $this->user()->can('edit users');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        $rules = [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'selected_roles' => ['array', 'min:1'],
            'selected_roles.*' => ['exists:roles,id'],
        ];

        // Password rules for creation or when updating password
        if ($this->isMethod('POST') || $this->filled('password')) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'confirmed',
            ];
            $rules['password_confirmation'] = ['required', 'string'];
        } else {
            $rules['password'] = ['nullable'];
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'Password confirmation does not match.',
            'selected_roles.required' => 'At least one role must be selected.',
            'selected_roles.min' => 'At least one role must be selected.',
            'selected_roles.*.exists' => 'One or more selected roles are invalid.',
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
            'selected_roles' => 'roles',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Prevent admin from removing their own admin role
            if ($this->user()->hasRole('admin') &&
                $this->route('user') &&
                $this->route('user')->id === $this->user()->id) {

                $selectedRoleIds = $this->input('selected_roles', []);
                $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();

                if ($adminRole && ! in_array($adminRole->id, $selectedRoleIds)) {
                    $validator->errors()->add('selected_roles', 'You cannot remove your own admin role.');
                }
            }

            // Validate email domain if needed
            if ($this->filled('email')) {
                $email = $this->input('email');
                $domain = substr(strrchr($email, '@'), 1);

                // Block common disposable email domains
                $blockedDomains = [
                    '10minutemail.com', 'tempmail.org', 'guerrillamail.com',
                    'mailinator.com', 'yopmail.com', 'throwaway.email',
                ];

                if (in_array(strtolower($domain), $blockedDomains)) {
                    $validator->errors()->add('email', 'Disposable email addresses are not allowed.');
                }
            }
        });
    }
}
