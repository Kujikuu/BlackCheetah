<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $staffId = $this->route('staffId');
        
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:staff,email,' . $staffId,
            'phone' => 'sometimes|nullable|string|max:20',
            'jobTitle' => 'sometimes|string|max:100',
            'department' => 'sometimes|nullable|string|max:100',
            'salary' => 'sometimes|nullable|numeric|min:0',
            'hireDate' => 'sometimes|date',
            'shiftStart' => 'sometimes|nullable|date_format:H:i',
            'shiftEnd' => 'sometimes|nullable|date_format:H:i',
            'status' => 'sometimes|in:working,leave,terminated,inactive',
            'employmentType' => 'sometimes|in:full_time,part_time,contract,temporary',
            'notes' => 'sometimes|nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'jobTitle.string' => 'Job title must be a string.',
            'jobTitle.max' => 'Job title cannot exceed 100 characters.',
            'department.string' => 'Department must be a string.',
            'department.max' => 'Department cannot exceed 100 characters.',
            'hireDate.date' => 'Hire date must be a valid date.',
            'shiftStart.date_format' => 'Shift start time must be in HH:MM format.',
            'shiftEnd.date_format' => 'Shift end time must be in HH:MM format.',
            'salary.numeric' => 'Salary must be a number.',
            'salary.min' => 'Salary must be at least 0.',
            'status.in' => 'Status must be one of: working, leave, terminated, inactive.',
            'employmentType.in' => 'Employment type must be one of: full_time, part_time, contract, temporary.',
            'notes.string' => 'Notes must be a string.',
        ];
    }
}
