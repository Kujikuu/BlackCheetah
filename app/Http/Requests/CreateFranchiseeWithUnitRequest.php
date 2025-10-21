<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFranchiseeWithUnitRequest extends FormRequest
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
            // Franchisee details
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',

            // Unit details
            'unit_name' => 'required|string|max:255',
            'unit_type' => 'required|in:store,kiosk,mobile,online,warehouse,office',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'size_sqft' => 'nullable|numeric|min:0',
            'monthly_rent' => 'nullable|numeric|min:0',
            'opening_date' => 'nullable|date',
            'status' => 'nullable|in:planning,construction,training,active,temporarily_closed,permanently_closed',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Franchisee name is required.',
            'name.string' => 'Franchisee name must be a string.',
            'name.max' => 'Franchisee name cannot exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'unit_name.required' => 'Unit name is required.',
            'unit_name.string' => 'Unit name must be a string.',
            'unit_name.max' => 'Unit name cannot exceed 255 characters.',
            'unit_type.required' => 'Unit type is required.',
            'unit_type.in' => 'Unit type must be one of: store, kiosk, mobile, online, warehouse, office.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'state_province.required' => 'State/Province is required.',
            'country.required' => 'Country is required.',
            'size_sqft.numeric' => 'Size must be a number.',
            'size_sqft.min' => 'Size must be at least 0.',
            'monthly_rent.numeric' => 'Monthly rent must be a number.',
            'monthly_rent.min' => 'Monthly rent must be at least 0.',
            'opening_date.date' => 'Opening date must be a valid date.',
            'status.in' => 'Status must be one of: planning, construction, training, active, temporarily_closed, permanently_closed.',
        ];
    }
}
