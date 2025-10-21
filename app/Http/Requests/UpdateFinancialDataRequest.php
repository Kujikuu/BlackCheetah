<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinancialDataRequest extends FormRequest
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
            'category' => 'required|in:sales,expense',
            'product' => 'required_if:category,sales|string|max:255',
            'date' => 'required|date',
            'quantitySold' => 'required_if:category,sales|integer|min:1',
            'expenseCategory' => 'required_if:category,expense|string|max:255',
            'amount' => 'required_if:category,expense|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category.required' => 'Category is required.',
            'category.in' => 'Category must be either sales or expense.',
            'product.required_if' => 'Product name is required for sales.',
            'product.string' => 'Product name must be a string.',
            'product.max' => 'Product name cannot exceed 255 characters.',
            'date.required' => 'Date is required.',
            'date.date' => 'Date must be a valid date.',
            'quantitySold.required_if' => 'Quantity sold is required for sales.',
            'quantitySold.integer' => 'Quantity sold must be an integer.',
            'quantitySold.min' => 'Quantity sold must be at least 1.',
            'expenseCategory.required_if' => 'Expense category is required for expenses.',
            'expenseCategory.string' => 'Expense category must be a string.',
            'expenseCategory.max' => 'Expense category cannot exceed 255 characters.',
            'amount.required_if' => 'Amount is required for expenses.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 0.',
            'description.string' => 'Description must be a string.',
        ];
    }
}
