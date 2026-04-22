<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->user_type === 'vendor';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = auth()->id();

        return [
            // User info
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'phone' => 'required|string|max:20|unique:users,phone,' . $userId,
            
            // Vendor info
            'business_name' => 'required|string|max:255',
            'business_phone' => 'required|string|max:20',
            'business_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'operating_hours' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already in use.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already in use.',
            'business_name.required' => 'Business name is required.',
            'business_phone.required' => 'Business phone is required.',
            'business_address.required' => 'Business address is required.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'picture.image' => 'The file must be an image.',
            'picture.mimes' => 'The image must be JPEG, PNG, JPG, or GIF.',
            'picture.max' => 'The image cannot exceed 2MB.',
        ];
    }
}
