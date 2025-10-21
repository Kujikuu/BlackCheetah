<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'jobTitle' => 'required|string|max:100',
            'department' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'hireDate' => 'required|date',
            'shiftStart' => 'nullable|date_format:H:i',
            'shiftEnd' => 'nullable|date_format:H:i',
            'status' => 'sometimes|in:Active,On Leave,Terminated,Inactive',
            'employmentType' => 'sometimes|in:full_time,part_time,contract,temporary',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Staff name is required.',
            'name.string' => 'Name must be a string.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'jobTitle.required' => 'Job title is required.',
            'jobTitle.string' => 'Job title must be a string.',
            'jobTitle.max' => 'Job title cannot exceed 100 characters.',
            'department.string' => 'Department must be a string.',
            'department.max' => 'Department cannot exceed 100 characters.',
            'hireDate.required' => 'Hire date is required.',
            'hireDate.date' => 'Hire date must be a valid date.',
            'shiftStart.date_format' => 'Shift start time must be in HH:MM format.',
            'shiftEnd.date_format' => 'Shift end time must be in HH:MM format.',
            'salary.numeric' => 'Salary must be a number.',
            'salary.min' => 'Salary must be at least 0.',
            'status.in' => 'Status must be one of: Active, On Leave, Terminated, Inactive.',
            'employmentType.in' => 'Employment type must be one of: full_time, part_time, contract, temporary.',
            'notes.string' => 'Notes must be a string.',
        ];
    }
}
