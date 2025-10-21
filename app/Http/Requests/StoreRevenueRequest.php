<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRevenueRequest extends FormRequest
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
            'type' => 'required|in:sales,fees,royalties,commissions,other',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'required|string|max:500',
            'revenue_date' => 'required|date',
            'period_year' => 'required|integer|min:2020|max:2030',
            'period_month' => 'required|integer|min:1|max:12',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_id' => 'required|exists:users,id',
            'source' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'required|in:pending,paid,partial,failed,refunded',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric',
            'line_items' => 'nullable|array',
            'metadata' => 'nullable|array',
            'status' => 'required|in:pending,verified,disputed,cancelled',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date|after:revenue_date',
            'parent_revenue_id' => 'nullable|exists:revenues,id',
            'attachments' => 'nullable|array',
            'is_auto_generated' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Revenue type is required.',
            'type.in' => 'Revenue type must be one of: sales, fees, royalties, commissions, other.',
            'category.required' => 'Category is required.',
            'category.string' => 'Category must be a string.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 0.',
            'currency.required' => 'Currency is required.',
            'currency.string' => 'Currency must be a string.',
            'currency.size' => 'Currency must be exactly 3 characters.',
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a string.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'revenue_date.required' => 'Revenue date is required.',
            'revenue_date.date' => 'Revenue date must be a valid date.',
            'period_year.required' => 'Period year is required.',
            'period_year.integer' => 'Period year must be an integer.',
            'period_year.min' => 'Period year must be at least 2020.',
            'period_year.max' => 'Period year cannot exceed 2030.',
            'period_month.required' => 'Period month is required.',
            'period_month.integer' => 'Period month must be an integer.',
            'period_month.min' => 'Period month must be at least 1.',
            'period_month.max' => 'Period month cannot exceed 12.',
            'franchise_id.exists' => 'Selected franchise does not exist.',
            'unit_id.exists' => 'Selected unit does not exist.',
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'source.string' => 'Source must be a string.',
            'source.max' => 'Source cannot exceed 100 characters.',
            'customer_name.string' => 'Customer name must be a string.',
            'customer_name.max' => 'Customer name cannot exceed 255 characters.',
            'invoice_number.string' => 'Invoice number must be a string.',
            'invoice_number.max' => 'Invoice number cannot exceed 100 characters.',
            'payment_method.string' => 'Payment method must be a string.',
            'payment_method.max' => 'Payment method cannot exceed 50 characters.',
            'payment_status.required' => 'Payment status is required.',
            'payment_status.in' => 'Payment status must be one of: pending, paid, partial, failed, refunded.',
            'tax_amount.numeric' => 'Tax amount must be a number.',
            'tax_amount.min' => 'Tax amount must be at least 0.',
            'discount_amount.numeric' => 'Discount amount must be a number.',
            'discount_amount.min' => 'Discount amount must be at least 0.',
            'net_amount.numeric' => 'Net amount must be a number.',
            'line_items.array' => 'Line items must be an array.',
            'metadata.array' => 'Metadata must be an array.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: pending, verified, disputed, cancelled.',
            'notes.string' => 'Notes must be a string.',
            'is_recurring.boolean' => 'Is recurring must be true or false.',
            'recurrence_type.in' => 'Recurrence type must be one of: daily, weekly, monthly, quarterly, yearly.',
            'recurrence_interval.integer' => 'Recurrence interval must be an integer.',
            'recurrence_interval.min' => 'Recurrence interval must be at least 1.',
            'recurrence_end_date.date' => 'Recurrence end date must be a valid date.',
            'recurrence_end_date.after' => 'Recurrence end date must be after revenue date.',
            'parent_revenue_id.exists' => 'Selected parent revenue does not exist.',
            'attachments.array' => 'Attachments must be an array.',
            'is_auto_generated.boolean' => 'Is auto generated must be true or false.',
        ];
    }
}
