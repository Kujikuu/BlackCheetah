<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnicalRequestRequest extends FormRequest
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
            'category' => 'required|in:hardware,software,network,pos_system,website,mobile_app,training,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'sometimes|in:open,in_progress,pending_info,resolved,closed,cancelled',
            'requester_id' => 'required|exists:users,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Request title is required.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'description.required' => 'Request description is required.',
            'category.required' => 'Category is required.',
            'category.in' => 'Invalid category selected.',
            'priority.required' => 'Priority level is required.',
            'priority.in' => 'Invalid priority level selected.',
            'status.in' => 'Invalid status selected.',
            'requester_id.required' => 'Requester ID is required.',
            'requester_id.exists' => 'Invalid requester selected.',
            'attachments.array' => 'Attachments must be an array.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.max' => 'Each attachment cannot exceed 10MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge(['status' => 'open']);
        }

        // Set requester_id to current user if not provided
        if (!$this->has('requester_id') && auth()->check()) {
            $this->merge(['requester_id' => auth()->id()]);
        }
    }
}
