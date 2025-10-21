<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLeadNoteRequest extends FormRequest
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
            'note' => 'required|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'note.required' => 'Note is required.',
            'note.string' => 'Note must be a string.',
            'note.max' => 'Note cannot exceed 1000 characters.',
        ];
    }
}
