<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoyaltyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'franchise_id' => ['required', 'integer', 'exists:franchises,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'franchisee_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'in:monthly,quarterly'],
            'period_year' => ['required', 'integer', 'min:2020', 'max:'.(date('Y') + 1)],
            'period_month' => ['nullable', 'integer', 'min:1', 'max:12', 'required_if:type,monthly'],
            'period_quarter' => ['nullable', 'integer', 'min:1', 'max:4', 'required_if:type,quarterly'],
            'base_revenue' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'royalty_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'royalty_amount' => ['nullable', 'numeric', 'min:0'],
            'marketing_fee_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'marketing_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'technology_fee_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'technology_fee_amount' => ['nullable', 'numeric', 'min:0'],
            'other_fees' => ['nullable', 'numeric', 'min:0'],
            'adjustments' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', 'string', 'in:pending,paid,overdue'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_auto_generated' => ['boolean'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'franchise_id.required' => 'Franchise selection is required.',
            'franchise_id.exists' => 'Selected franchise does not exist.',
            'franchisee_id.required' => 'Franchisee selection is required.',
            'franchisee_id.exists' => 'Selected franchisee does not exist.',
            'unit_id.exists' => 'Selected unit does not exist.',
            'type.required' => 'Royalty type is required.',
            'type.in' => 'Royalty type must be either monthly or quarterly.',
            'period_year.required' => 'Period year is required.',
            'period_year.min' => 'Period year must be at least 2020.',
            'period_year.max' => 'Period year cannot be more than next year.',
            'period_month.required_if' => 'Period month is required for monthly royalties.',
            'period_month.min' => 'Period month must be between 1 and 12.',
            'period_month.max' => 'Period month must be between 1 and 12.',
            'period_quarter.required_if' => 'Period quarter is required for quarterly royalties.',
            'period_quarter.min' => 'Period quarter must be between 1 and 4.',
            'period_quarter.max' => 'Period quarter must be between 1 and 4.',
            'base_revenue.required' => 'Base revenue is required.',
            'base_revenue.numeric' => 'Base revenue must be a valid number.',
            'base_revenue.min' => 'Base revenue cannot be negative.',
            'base_revenue.max' => 'Base revenue is too large.',
            'royalty_rate.required' => 'Royalty rate is required.',
            'royalty_rate.numeric' => 'Royalty rate must be a valid number.',
            'royalty_rate.min' => 'Royalty rate cannot be negative.',
            'royalty_rate.max' => 'Royalty rate cannot exceed 100%.',
            'marketing_fee_rate.required' => 'Marketing fee rate is required.',
            'marketing_fee_rate.numeric' => 'Marketing fee rate must be a valid number.',
            'marketing_fee_rate.min' => 'Marketing fee rate cannot be negative.',
            'marketing_fee_rate.max' => 'Marketing fee rate cannot exceed 100%.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date cannot be in the past.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be pending, paid, or overdue.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'notes.max' => 'Notes cannot exceed 2000 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'franchise_id' => 'franchise',
            'franchisee_id' => 'franchisee',
            'unit_id' => 'unit',
            'period_year' => 'period year',
            'period_month' => 'period month',
            'period_quarter' => 'period quarter',
            'base_revenue' => 'base revenue',
            'royalty_rate' => 'royalty rate',
            'marketing_fee_rate' => 'marketing fee rate',
            'technology_fee_rate' => 'technology fee rate',
            'technology_fee_amount' => 'technology fee amount',
            'other_fees' => 'other fees',
            'due_date' => 'due date',
        ];
    }
}
