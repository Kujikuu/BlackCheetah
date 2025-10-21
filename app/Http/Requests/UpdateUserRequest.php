<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');
        
        return [
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'status' => 'required|in:active,pending,inactive',
            'franchiseName' => 'required_if:role,franchisor|string|max:255',
            'plan' => 'required_if:role,franchisor|in:Basic,Pro,Enterprise',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'fullName.required' => 'Full name is required.',
            'fullName.string' => 'Full name must be a string.',
            'fullName.max' => 'Full name cannot exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'city.string' => 'City must be a string.',
            'city.max' => 'City cannot exceed 100 characters.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be one of: active, pending, inactive.',
            'franchiseName.required_if' => 'Franchise name is required for franchisors.',
            'franchiseName.string' => 'Franchise name must be a string.',
            'franchiseName.max' => 'Franchise name cannot exceed 255 characters.',
            'plan.required_if' => 'Plan is required for franchisors.',
            'plan.in' => 'Plan must be one of: Basic, Pro, Enterprise.',
        ];
    }
}
