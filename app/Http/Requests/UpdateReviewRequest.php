<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
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
            'customerName' => 'sometimes|string|max:255',
            'customerEmail' => 'sometimes|nullable|email|max:255',
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
            'date' => 'sometimes|date',
            'sentiment' => 'sometimes|in:positive,neutral,negative',
            'status' => 'sometimes|in:published,draft,archived',
            'internal_notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customerName.string' => 'Customer name must be a string.',
            'customerName.max' => 'Customer name cannot exceed 255 characters.',
            'customerEmail.email' => 'Customer email must be a valid email address.',
            'customerEmail.max' => 'Customer email cannot exceed 255 characters.',
            'rating.integer' => 'Rating must be an integer.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating must be at most 5.',
            'comment.string' => 'Comment must be a string.',
            'comment.max' => 'Comment cannot exceed 1000 characters.',
            'date.date' => 'Date must be a valid date.',
            'sentiment.in' => 'Sentiment must be one of: positive, neutral, negative.',
            'status.in' => 'Status must be one of: published, draft, archived.',
            'internal_notes.string' => 'Internal notes must be a string.',
            'internal_notes.max' => 'Internal notes cannot exceed 500 characters.',
        ];
    }
}
