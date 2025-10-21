<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFranchiseRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'franchisor_id' => 'required|exists:users,id',
            'business_name' => 'required|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'industry' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'logo' => 'nullable|string|max:255',
            'business_registration_number' => 'required|string|unique:franchises,business_registration_number',
            'tax_id' => 'nullable|string|max:255',
            'business_type' => 'required|in:corporation,llc,partnership,sole_proprietorship',
            'established_date' => 'nullable|date',
            'headquarters_country' => 'required|string|max:100',
            'headquarters_city' => 'required|string|max:100',
            'headquarters_address' => 'required|string',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255|unique:franchises,contact_email',
            'franchise_fee' => 'nullable|numeric|min:0',
            'royalty_percentage' => 'nullable|numeric|min:0|max:100',
            'marketing_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,pending_approval,suspended',
            'plan' => 'nullable|string|max:255',
            'business_hours' => 'nullable|array',
            'social_media' => 'nullable|array',
            'documents' => 'nullable|array'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'franchisor_id.required' => 'Franchisor is required.',
            'franchisor_id.exists' => 'Selected franchisor does not exist.',
            'business_name.required' => 'Business name is required.',
            'business_name.max' => 'Business name must not exceed 255 characters.',
            'industry.required' => 'Industry is required.',
            'business_registration_number.required' => 'Business registration number is required.',
            'business_registration_number.unique' => 'This business registration number already exists.',
            'contact_email.required' => 'Contact email is required.',
            'contact_email.email' => 'Contact email must be a valid email address.',
            'contact_email.unique' => 'This contact email already exists.',
            'royalty_percentage.max' => 'Royalty percentage cannot exceed 100%.',
            'marketing_fee_percentage.max' => 'Marketing fee percentage cannot exceed 100%.',
        ];
    }
}
