<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFranchiseStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'franchisor';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $staffId = $this->route('staff');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('staff', 'email')->ignore($staffId),
            ],
            'phone' => 'nullable|string|max:20',
            'job_title' => 'sometimes|required|string|max:255',
            'department' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'sometimes|required|date',
            'shift_start' => 'nullable|date_format:H:i',
            'shift_end' => 'nullable|date_format:H:i',
            'status' => 'sometimes|required|in:active,on_leave,terminated,inactive',
            'employment_type' => 'sometimes|required|in:full_time,part_time,contract,temporary',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Staff name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'job_title.required' => 'Job title is required.',
            'hire_date.required' => 'Hire date is required.',
            'status.required' => 'Employment status is required.',
            'employment_type.required' => 'Employment type is required.',
        ];
    }
}
