<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarketplaceInquiryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'inquiry_type' => 'required|in:franchise,property',
            'franchise_id' => 'required_if:inquiry_type,franchise|nullable|exists:franchises,id',
            'property_id' => 'required_if:inquiry_type,property|nullable|exists:properties,id',
            'message' => 'required|string|max:2000',
            'investment_budget' => 'nullable|string|max:100',
            'preferred_location' => 'nullable|string|max:255',
            'timeline' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'phone.required' => 'Phone number is required.',
            'inquiry_type.required' => 'Please select an inquiry type.',
            'inquiry_type.in' => 'Invalid inquiry type.',
            'franchise_id.required_if' => 'Please select a franchise.',
            'property_id.required_if' => 'Please select a property.',
            'message.required' => 'Message is required.',
            'message.max' => 'Message cannot exceed 2000 characters.',
        ];
    }
}
