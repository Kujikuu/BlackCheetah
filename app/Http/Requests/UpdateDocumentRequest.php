<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'type' => ['sometimes', 'required', 'string', 'in:contract,invoice,receipt,certificate,license,manual,policy,report,other'],
            'expiry_date' => ['sometimes', 'nullable', 'date', 'after:today'],
            'is_confidential' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'metadata.*' => ['string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Document name is required.',
            'name.max' => 'Document name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'type.required' => 'Document type is required.',
            'type.in' => 'Invalid document type selected.',
            'expiry_date.date' => 'Expiry date must be a valid date.',
            'expiry_date.after' => 'Expiry date must be in the future.',
            'is_confidential.boolean' => 'Confidential status must be true or false.',
            'metadata.array' => 'Metadata must be an array.',
            'metadata.*.string' => 'Each metadata value must be a string.',
            'metadata.*.max' => 'Each metadata value cannot exceed 500 characters.',
        ];
    }
}
