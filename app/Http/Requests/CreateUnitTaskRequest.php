<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUnitTaskRequest extends FormRequest
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
            'description' => 'required|string|max:1000',
            'category' => 'required|in:onboarding,training,compliance,maintenance,marketing,operations,finance,support,other',
            'assignedTo' => 'required|string|max:255',
            'startDate' => 'nullable|date',
            'dueDate' => 'required|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled,on_hold',
            'estimatedHours' => 'nullable|numeric|min:0',
            'actualHours' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.string' => 'Task title must be a string.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'description.required' => 'Task description is required.',
            'description.string' => 'Task description must be a string.',
            'description.max' => 'Task description cannot exceed 1000 characters.',
            'category.required' => 'Task category is required.',
            'category.in' => 'Task category must be one of: onboarding, training, compliance, maintenance, marketing, operations, finance, support, other.',
            'assignedTo.required' => 'Assigned to field is required.',
            'assignedTo.string' => 'Assigned to must be a string.',
            'assignedTo.max' => 'Assigned to cannot exceed 255 characters.',
            'startDate.date' => 'Start date must be a valid date.',
            'dueDate.required' => 'Due date is required.',
            'dueDate.date' => 'Due date must be a valid date.',
            'priority.required' => 'Priority is required.',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'status.in' => 'Status must be one of: pending, in_progress, completed, cancelled, on_hold.',
            'estimatedHours.numeric' => 'Estimated hours must be a number.',
            'estimatedHours.min' => 'Estimated hours must be at least 0.',
            'actualHours.numeric' => 'Actual hours must be a number.',
            'actualHours.min' => 'Actual hours must be at least 0.',
        ];
    }
}
