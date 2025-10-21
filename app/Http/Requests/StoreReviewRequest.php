<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'customerName' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'date' => 'sometimes|date',
            'customerEmail' => 'nullable|email|max:255',
            'sentiment' => 'sometimes|in:positive,neutral,negative',
            'status' => 'sometimes|in:published,draft,archived',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customerName.required' => 'Customer name is required.',
            'customerName.string' => 'Customer name must be a string.',
            'customerName.max' => 'Customer name cannot exceed 255 characters.',
            'rating.required' => 'Rating is required.',
            'rating.integer' => 'Rating must be an integer.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating must be at most 5.',
            'comment.required' => 'Comment is required.',
            'comment.string' => 'Comment must be a string.',
            'comment.max' => 'Comment cannot exceed 1000 characters.',
            'date.date' => 'Date must be a valid date.',
            'customerEmail.email' => 'Customer email must be a valid email address.',
            'customerEmail.max' => 'Customer email cannot exceed 255 characters.',
            'sentiment.in' => 'Sentiment must be one of: positive, neutral, negative.',
            'status.in' => 'Status must be one of: published, draft, archived.',
        ];
    }
}
