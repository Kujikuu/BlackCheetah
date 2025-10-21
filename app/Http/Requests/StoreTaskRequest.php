<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:Operations,Training,Maintenance,Marketing,Finance,HR,Quality Control,Customer Service',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date|after_or_equal:today',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'attachments' => 'nullable|array',
            'checklist' => 'nullable|array',
            'dependencies' => 'nullable|array',
            'tags' => 'nullable|array',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'description.required' => 'Task description is required.',
            'category.required' => 'Task category is required.',
            'category.in' => 'Invalid task category selected.',
            'priority.required' => 'Task priority is required.',
            'priority.in' => 'Invalid priority level selected.',
            'status.required' => 'Task status is required.',
            'status.in' => 'Invalid task status selected.',
            'franchise_id.exists' => 'Invalid franchise selected.',
            'unit_id.exists' => 'Invalid unit selected.',
            'assigned_to.exists' => 'Invalid user selected for assignment.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be today or in the future.',
            'estimated_hours.numeric' => 'Estimated hours must be a number.',
            'estimated_hours.min' => 'Estimated hours cannot be negative.',
            'actual_hours.numeric' => 'Actual hours must be a number.',
            'actual_hours.min' => 'Actual hours cannot be negative.',
        ];
    }
}
