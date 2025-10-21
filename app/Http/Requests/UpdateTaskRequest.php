<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|in:Operations,Training,Maintenance,Marketing,Finance,HR,Quality Control,Customer Service',
            'priority' => 'sometimes|in:low,medium,high',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
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
            'title.string' => 'Task title must be a string.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'description.string' => 'Task description must be a string.',
            'category.in' => 'Invalid task category selected.',
            'priority.in' => 'Invalid priority level selected.',
            'status.in' => 'Invalid task status selected.',
            'franchise_id.exists' => 'Invalid franchise selected.',
            'unit_id.exists' => 'Invalid unit selected.',
            'assigned_to.exists' => 'Invalid user selected for assignment.',
            'due_date.date' => 'Due date must be a valid date.',
            'estimated_hours.numeric' => 'Estimated hours must be a number.',
            'estimated_hours.min' => 'Estimated hours cannot be negative.',
            'actual_hours.numeric' => 'Actual hours must be a number.',
            'actual_hours.min' => 'Actual hours cannot be negative.',
        ];
    }
}
