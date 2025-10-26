<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFranchiseRequest extends FormRequest
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
            'personalInfo.contactNumber' => 'required|string|max:20',
            'personalInfo.nationality' => 'required|string|max:100',
            'personalInfo.state' => 'required|string|max:100',
            'personalInfo.city' => 'required|string|max:100',
            'personalInfo.address' => 'required|string|max:500',

            // Franchise Details
            'franchiseDetails.franchiseDetails.franchiseName' => 'required|string|max:255',
            'franchiseDetails.franchiseDetails.website' => 'nullable|url|max:255',
            'franchiseDetails.franchiseDetails.logo' => 'nullable|string|max:500',

            // Legal Details
            'franchiseDetails.legalDetails.legalEntityName' => 'required|string|max:255',
            'franchiseDetails.legalDetails.businessStructure' => 'required|in:corporation,llc,partnership,sole_proprietorship',
            'franchiseDetails.legalDetails.taxId' => 'nullable|string|max:50',
            'franchiseDetails.legalDetails.industry' => 'required|string|max:100',
            'franchiseDetails.legalDetails.fundingAmount' => 'nullable|string|max:100',
            'franchiseDetails.legalDetails.fundingSource' => 'nullable|string|max:100',

            // Contact Details
            'franchiseDetails.contactDetails.contactNumber' => 'required|string|max:20',
            'franchiseDetails.contactDetails.email' => 'required|email|max:255',
            'franchiseDetails.contactDetails.address' => 'required|string|max:500',
            'franchiseDetails.contactDetails.nationality' => 'required|string|max:100',
            'franchiseDetails.contactDetails.state' => 'required|string|max:100',
            'franchiseDetails.contactDetails.city' => 'required|string|max:100',

            // Financial Details
            'franchiseDetails.financialDetails.franchiseFee' => 'nullable|numeric|min:0',
            'franchiseDetails.financialDetails.royaltyPercentage' => 'nullable|numeric|min:0|max:100',
            'franchiseDetails.financialDetails.marketingFeePercentage' => 'nullable|numeric|min:0|max:100',

            // Documents (optional for now)
            'documents.fdd' => 'nullable|string',
            'documents.franchiseAgreement' => 'nullable|string',
            'documents.operationsManual' => 'nullable|string',
            'documents.brandGuidelines' => 'nullable|string',
            'documents.legalDocuments' => 'nullable|string',

            // Review Complete
            'reviewComplete.termsAccepted' => 'required|boolean|accepted',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'personalInfo.contactNumber.required' => 'Contact number is required.',
            'personalInfo.nationality.required' => 'Nationality is required.',
            'personalInfo.state.required' => 'State is required.',
            'personalInfo.city.required' => 'City is required.',
            'personalInfo.address.required' => 'Address is required.',
            'franchiseDetails.franchiseDetails.franchiseName.required' => 'Franchise name is required.',
            'franchiseDetails.legalDetails.legalEntityName.required' => 'Legal entity name is required.',
            'franchiseDetails.legalDetails.businessStructure.required' => 'Business structure is required.',
            'franchiseDetails.legalDetails.industry.required' => 'Industry is required.',
        ];
    }
}
