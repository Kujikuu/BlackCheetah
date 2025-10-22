<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFranchiseRequest extends FormRequest
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
            // Personal Info
            'personalInfo.contactNumber' => 'sometimes|nullable|string|max:20',
            'personalInfo.nationality' => 'sometimes|nullable|string|max:100',
            'personalInfo.state' => 'sometimes|nullable|string|max:100',
            'personalInfo.city' => 'sometimes|nullable|string|max:100',
            'personalInfo.address' => 'sometimes|nullable|string|max:500',

            // Franchise Details
            'franchiseDetails.franchiseDetails.franchiseName' => 'sometimes|nullable|string|max:255',
            'franchiseDetails.franchiseDetails.website' => 'sometimes|nullable|url|max:255',
            'franchiseDetails.franchiseDetails.logo' => 'sometimes|nullable|string',

            // Legal Details
            'franchiseDetails.legalDetails.legalEntityName' => 'sometimes|nullable|string|max:255',
            'franchiseDetails.legalDetails.businessStructure' => 'sometimes|nullable|in:corporation,llc,partnership,sole_proprietorship',
            'franchiseDetails.legalDetails.taxId' => 'sometimes|nullable|string|max:50',
            'franchiseDetails.legalDetails.industry' => 'sometimes|nullable|string|max:100',
            'franchiseDetails.legalDetails.fundingAmount' => 'sometimes|nullable|string|max:100',
            'franchiseDetails.legalDetails.fundingSource' => 'sometimes|nullable|string|max:100',

            // Contact Details
            'franchiseDetails.contactDetails.contactNumber' => 'sometimes|nullable|string|max:20',
            'franchiseDetails.contactDetails.email' => 'sometimes|nullable|email|max:255',
            'franchiseDetails.contactDetails.address' => 'sometimes|nullable|string|max:500',
            'franchiseDetails.contactDetails.nationality' => 'sometimes|nullable|string|max:100',
            'franchiseDetails.contactDetails.state' => 'sometimes|nullable|string|max:100',
            'franchiseDetails.contactDetails.city' => 'sometimes|nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'personalInfo.contactNumber.string' => 'Contact number must be a string.',
            'personalInfo.contactNumber.max' => 'Contact number cannot exceed 20 characters.',
            'personalInfo.nationality.string' => 'Nationality must be a string.',
            'personalInfo.nationality.max' => 'Nationality cannot exceed 100 characters.',
            'personalInfo.state.string' => 'State must be a string.',
            'personalInfo.state.max' => 'State cannot exceed 100 characters.',
            'personalInfo.city.string' => 'City must be a string.',
            'personalInfo.city.max' => 'City cannot exceed 100 characters.',
            'personalInfo.address.string' => 'Address must be a string.',
            'personalInfo.address.max' => 'Address cannot exceed 500 characters.',
            'franchiseDetails.franchiseDetails.franchiseName.string' => 'Franchise name must be a string.',
            'franchiseDetails.franchiseDetails.franchiseName.max' => 'Franchise name cannot exceed 255 characters.',
            'franchiseDetails.franchiseDetails.website.url' => 'Website must be a valid URL.',
            'franchiseDetails.franchiseDetails.website.max' => 'Website cannot exceed 255 characters.',
            'franchiseDetails.legalDetails.legalEntityName.string' => 'Legal entity name must be a string.',
            'franchiseDetails.legalDetails.legalEntityName.max' => 'Legal entity name cannot exceed 255 characters.',
            'franchiseDetails.legalDetails.businessStructure.in' => 'Business structure must be one of: corporation, llc, partnership, sole_proprietorship.',
            'franchiseDetails.legalDetails.taxId.string' => 'Tax ID must be a string.',
            'franchiseDetails.legalDetails.taxId.max' => 'Tax ID cannot exceed 50 characters.',
            'franchiseDetails.legalDetails.industry.string' => 'Industry must be a string.',
            'franchiseDetails.legalDetails.industry.max' => 'Industry cannot exceed 100 characters.',
            'franchiseDetails.contactDetails.contactNumber.string' => 'Contact number must be a string.',
            'franchiseDetails.contactDetails.contactNumber.max' => 'Contact number cannot exceed 20 characters.',
            'franchiseDetails.contactDetails.email.email' => 'Contact email must be a valid email address.',
            'franchiseDetails.contactDetails.email.max' => 'Contact email cannot exceed 255 characters.',
            'franchiseDetails.contactDetails.address.string' => 'Contact address must be a string.',
            'franchiseDetails.contactDetails.address.max' => 'Contact address cannot exceed 500 characters.',
            'franchiseDetails.contactDetails.country.string' => 'Contact country must be a string.',
            'franchiseDetails.contactDetails.country.max' => 'Contact country cannot exceed 100 characters.',
            'franchiseDetails.contactDetails.state.string' => 'Contact state must be a string.',
            'franchiseDetails.contactDetails.state.max' => 'Contact state cannot exceed 100 characters.',
            'franchiseDetails.contactDetails.city.string' => 'Contact city must be a string.',
            'franchiseDetails.contactDetails.city.max' => 'Contact city cannot exceed 100 characters.',
        ];
    }
}