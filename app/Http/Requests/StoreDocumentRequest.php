<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:10240'], // 10MB max
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:contract,agreement,manual,certificate,report,other'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'is_confidential' => ['boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A document file is required.',
            'file.max' => 'The document file must not exceed 10MB.',
            'name.required' => 'Document name is required.',
            'name.max' => 'Document name must not exceed 255 characters.',
            'type.required' => 'Document type is required.',
            'type.in' => 'Document type must be one of: contract, agreement, manual, certificate, report, or other.',
            'expiry_date.after' => 'Expiry date must be in the future.',
        ];
    }
}
