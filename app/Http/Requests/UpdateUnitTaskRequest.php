<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitTaskRequest extends FormRequest
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
            'description' => 'sometimes|string|max:1000',
            'category' => 'sometimes|string|max:100',
            'assignedTo' => 'sometimes|string|max:255',
            'startDate' => 'sometimes|date',
            'dueDate' => 'sometimes|date',
            'priority' => 'sometimes|in:low,medium,high,urgent',
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
            'title.string' => 'Task title must be a string.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'description.string' => 'Task description must be a string.',
            'description.max' => 'Task description cannot exceed 1000 characters.',
            'category.string' => 'Task category must be a string.',
            'category.max' => 'Task category cannot exceed 100 characters.',
            'assignedTo.string' => 'Assigned to must be a string.',
            'assignedTo.max' => 'Assigned to cannot exceed 255 characters.',
            'startDate.date' => 'Start date must be a valid date.',
            'dueDate.date' => 'Due date must be a valid date.',
            'priority.in' => 'Priority must be one of: low, medium, high, urgent.',
            'status.in' => 'Status must be one of: pending, in_progress, completed, cancelled, on_hold.',
            'estimatedHours.numeric' => 'Estimated hours must be a number.',
            'estimatedHours.min' => 'Estimated hours must be at least 0.',
            'actualHours.numeric' => 'Actual hours must be a number.',
            'actualHours.min' => 'Actual hours must be at least 0.',
        ];
    }
}
