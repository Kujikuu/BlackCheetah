<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'broker';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:5000',
            'property_type' => 'sometimes|required|string|in:retail,office,kiosk,food_court,standalone',
            'size_sqm' => 'sometimes|required|numeric|min:0',
            'state_province' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'monthly_rent' => 'sometimes|required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'lease_term_months' => 'nullable|integer|min:1',
            'available_from' => 'nullable|date',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'status' => 'nullable|string|in:available,under_negotiation,leased,unavailable',
            'contact_info' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Property title is required.',
            'description.required' => 'Property description is required.',
            'property_type.required' => 'Property type is required.',
            'property_type.in' => 'Invalid property type selected.',
            'size_sqm.required' => 'Property size is required.',
            'monthly_rent.required' => 'Monthly rent is required.',
            'state_province.required' => 'State/Province is required.',
            'city.required' => 'City is required.',
            'address.required' => 'Address is required.',
        ];
    }
}
