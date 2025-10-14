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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Map frontend billing frequency to database fee type
        // Frontend sends 'type' with value 'monthly' or 'quarterly' (billing frequency)
        // Database expects 'type' with value 'royalty', 'marketing_fee', 'technology_fee', or 'other' (fee type)
        // We'll store the billing frequency separately and set type to 'royalty'
        if ($this->has('type') && in_array($this->input('type'), ['monthly', 'quarterly'])) {
            $this->merge([
                'billing_frequency' => $this->input('type'), // Store original for validation
                'type' => 'royalty', // Override with correct database type
            ]);
        } else {
            // If type is not provided or is already a valid database type, use 'royalty' as default
            $this->merge([
                'type' => 'royalty',
            ]);
        }

        // Calculate period dates based on period_year and period_month
        if ($this->has('period_year') && $this->has('period_month')) {
            $year = $this->input('period_year');
            $month = $this->input('period_month');

            $this->merge([
                'period_start_date' => date('Y-m-d', strtotime("$year-$month-01")),
                'period_end_date' => date('Y-m-t', strtotime("$year-$month-01")),
            ]);
        }

        // Map frontend field names to backend field names
        if ($this->has('base_revenue')) {
            $this->merge(['gross_revenue' => $this->input('base_revenue')]);
        }
        if ($this->has('royalty_rate')) {
            $this->merge(['royalty_percentage' => $this->input('royalty_rate')]);
        }
        if ($this->has('marketing_fee_rate')) {
            $this->merge(['marketing_fee_percentage' => $this->input('marketing_fee_rate')]);
        }

        // Set is_auto_generated to false by default for manually created royalties
        if (! $this->has('is_auto_generated')) {
            $this->merge(['is_auto_generated' => false]);
        }

        // Generate royalty number if not provided
        if (! $this->has('royalty_number')) {
            $this->merge([
                'royalty_number' => 'ROY' . date('Ymd') . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            ]);
        }
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
            'billing_frequency' => ['required', 'string', 'in:monthly,quarterly'],
            'period_year' => ['required', 'integer', 'min:2020', 'max:' . (date('Y') + 1)],
            'period_month' => ['nullable', 'integer', 'min:1', 'max:12', 'required_if:billing_frequency,monthly'],
            'period_quarter' => ['nullable', 'integer', 'min:1', 'max:4', 'required_if:billing_frequency,quarterly'],
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
            'billing_frequency.required' => 'Billing frequency is required.',
            'billing_frequency.in' => 'Billing frequency must be either monthly or quarterly.',
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
