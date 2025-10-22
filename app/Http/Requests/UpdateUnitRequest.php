<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
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
            'unit_name' => 'sometimes|string|max:255',
            'franchise_id' => 'sometimes|exists:franchises,id',
            'unit_type' => 'sometimes|in:store,kiosk,mobile,online,warehouse,office',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'state_province' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'nationality' => 'sometimes|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'franchisee_id' => 'nullable|exists:users,id',
            'size_sqft' => 'nullable|numeric|min:0',
            'initial_investment' => 'nullable|numeric|min:0',
            'opening_date' => 'nullable|date',
            'status' => 'sometimes|in:planning,construction,training,active,temporarily_closed,permanently_closed',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date|after:lease_start_date',
            'monthly_revenue' => 'nullable|numeric|min:0',
            'monthly_expenses' => 'nullable|numeric|min:0',
            'operating_hours' => 'nullable|array',
            'amenities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'notes' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'unit_name.string' => 'Unit name must be a string.',
            'unit_name.max' => 'Unit name cannot exceed 255 characters.',
            'franchise_id.exists' => 'Selected franchise does not exist.',
            'unit_type.in' => 'Unit type must be one of: store, kiosk, mobile, online, warehouse, office.',
            'address.string' => 'Address must be a string.',
            'city.string' => 'City must be a string.',
            'city.max' => 'City cannot exceed 100 characters.',
            'state_province.string' => 'State/Province must be a string.',
            'state_province.max' => 'State/Province cannot exceed 100 characters.',
            'postal_code.string' => 'Postal code must be a string.',
            'postal_code.max' => 'Postal code cannot exceed 20 characters.',
            'nationality.string' => 'Nationality must be a string.',
            'nationality.max' => 'Nationality cannot exceed 100 characters.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'franchisee_id.exists' => 'Selected franchisee does not exist.',
            'size_sqft.numeric' => 'Size must be a number.',
            'size_sqft.min' => 'Size must be at least 0.',
            'initial_investment.numeric' => 'Initial investment must be a number.',
            'initial_investment.min' => 'Initial investment must be at least 0.',
            'opening_date.date' => 'Opening date must be a valid date.',
            'status.in' => 'Status must be one of: planning, construction, training, active, temporarily_closed, permanently_closed.',
            'lease_start_date.date' => 'Lease start date must be a valid date.',
            'lease_end_date.date' => 'Lease end date must be a valid date.',
            'lease_end_date.after' => 'Lease end date must be after lease start date.',
            'monthly_revenue.numeric' => 'Monthly revenue must be a number.',
            'monthly_revenue.min' => 'Monthly revenue must be at least 0.',
            'monthly_expenses.numeric' => 'Monthly expenses must be a number.',
            'monthly_expenses.min' => 'Monthly expenses must be at least 0.',
            'operating_hours.array' => 'Operating hours must be an array.',
            'amenities.array' => 'Amenities must be an array.',
            'equipment.array' => 'Equipment must be an array.',
            'notes.string' => 'Notes must be a string.',
        ];
    }
}
