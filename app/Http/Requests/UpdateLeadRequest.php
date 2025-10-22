<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
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
        $leadId = $this->route('lead')->id ?? null;

        return [
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('leads', 'email')->ignore($leadId)
            ],
            'phone' => 'sometimes|required|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'city' => 'sometimes|required|string|max:100',
            'nationality' => 'sometimes|required|string|max:100',
            'lead_source' => 'sometimes|required|in:website,referral,social_media,advertisement,cold_call,event,other',
            'status' => 'sometimes|required|in:new,contacted,qualified,proposal_sent,negotiating,closed_won,closed_lost',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'franchise_id' => 'nullable|exists:franchises,id',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_investment' => 'nullable|numeric|min:0',
            'franchise_fee_quoted' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'expected_decision_date' => 'nullable|date',
            'last_contact_date' => 'nullable|date',
            'next_follow_up_date' => 'nullable|date',
            'contact_attempts' => 'nullable|integer|min:0',
            'interests' => 'nullable|array',
            'documents' => 'nullable|array',
            'communication_log' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered as a lead.',
            'phone.required' => 'Phone number is required.',
            'city.required' => 'City is required.',
            'nationality.required' => 'Nationality is required.',
            'lead_source.required' => 'Lead source is required.',
            'lead_source.in' => 'Invalid lead source selected.',
            'status.required' => 'Lead status is required.',
            'status.in' => 'Invalid lead status selected.',
            'priority.required' => 'Lead priority is required.',
            'priority.in' => 'Invalid priority level selected.',
        ];
    }
}
