<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkLeadAsLostRequest extends FormRequest
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
            'lost_reason' => 'required|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'lost_reason.required' => 'Lost reason is required.',
            'lost_reason.string' => 'Lost reason must be a string.',
            'lost_reason.max' => 'Lost reason cannot exceed 255 characters.',
        ];
    }
}
