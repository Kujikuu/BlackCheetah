<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'type' => 'required|in:revenue,expense,transfer,refund,adjustment',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'required|string|max:500',
            'transaction_date' => 'required|date',
            'franchise_id' => 'nullable|exists:franchises,id',
            'unit_id' => 'nullable|exists:units,id',
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric',
            'status' => 'required|in:pending,completed,cancelled,refunded',
            'notes' => 'nullable|string',
            'metadata' => 'nullable|array',
            'is_recurring' => 'boolean',
            'recurrence_type' => 'nullable|in:daily,weekly,monthly,quarterly,yearly',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_end_date' => 'nullable|date|after:transaction_date',
            'parent_transaction_id' => 'nullable|exists:transactions,id',
            'attachments' => 'nullable|array'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Transaction type is required.',
            'type.in' => 'Transaction type must be one of: revenue, expense, transfer, refund, adjustment.',
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
            'transaction_date.required' => 'Transaction date is required.',
            'transaction_date.date' => 'Transaction date must be a valid date.',
            'franchise_id.exists' => 'Selected franchise does not exist.',
            'unit_id.exists' => 'Selected unit does not exist.',
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'Selected user does not exist.',
            'payment_method.string' => 'Payment method must be a string.',
            'payment_method.max' => 'Payment method cannot exceed 50 characters.',
            'reference_number.string' => 'Reference number must be a string.',
            'reference_number.max' => 'Reference number cannot exceed 100 characters.',
            'invoice_number.string' => 'Invoice number must be a string.',
            'invoice_number.max' => 'Invoice number cannot exceed 100 characters.',
            'tax_amount.numeric' => 'Tax amount must be a number.',
            'tax_amount.min' => 'Tax amount must be at least 0.',
            'discount_amount.numeric' => 'Discount amount must be a number.',
            'discount_amount.min' => 'Discount amount must be at least 0.',
            'net_amount.numeric' => 'Net amount must be a number.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: pending, completed, cancelled, refunded.',
            'notes.string' => 'Notes must be a string.',
            'metadata.array' => 'Metadata must be an array.',
            'is_recurring.boolean' => 'Is recurring must be true or false.',
            'recurrence_type.in' => 'Recurrence type must be one of: daily, weekly, monthly, quarterly, yearly.',
            'recurrence_interval.integer' => 'Recurrence interval must be an integer.',
            'recurrence_interval.min' => 'Recurrence interval must be at least 1.',
            'recurrence_end_date.date' => 'Recurrence end date must be a valid date.',
            'recurrence_end_date.after' => 'Recurrence end date must be after transaction date.',
            'parent_transaction_id.exists' => 'Selected parent transaction does not exist.',
            'attachments.array' => 'Attachments must be an array.',
        ];
    }
}
