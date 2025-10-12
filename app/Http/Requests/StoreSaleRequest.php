<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'product' => ['required', 'string', 'max:255'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'franchise_id' => ['sometimes', 'exists:franchises,id'],
            'unit_id' => ['sometimes', 'exists:units,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product.required' => 'Product name is required',
            'unit_price.required' => 'Unit price is required',
            'unit_price.numeric' => 'Unit price must be a number',
            'unit_price.min' => 'Unit price must be at least 0',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be a whole number',
            'quantity.min' => 'Quantity must be at least 1',
            'date.required' => 'Sale date is required',
            'date.date' => 'Sale date must be a valid date',
            'date.before_or_equal' => 'Sale date cannot be in the future',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'unit_price' => 'unit price',
            'customer_name' => 'customer name',
            'customer_email' => 'customer email',
            'customer_phone' => 'customer phone',
        ];
    }
}
