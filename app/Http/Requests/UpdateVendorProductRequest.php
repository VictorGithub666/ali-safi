<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorProductRequest extends FormRequest
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
        $productId = $this->route('product')->id;

        return [
            'name' => 'required|string|max:255|unique:products,name,' . $productId,
            'description' => 'required|string|max:1000|min:10',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0.01|max:999999.99',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'stock_quantity' => 'required|integer|min:0|max:999999',
            'is_available' => 'boolean',
            'custom_price' => 'nullable|numeric|min:0.01|max:999999.99',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.unique' => 'A product with this name already exists.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.required' => 'Product description is required.',
            'description.min' => 'Description must be at least 10 characters long.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Product price must be a valid number.',
            'price.min' => 'Product price must be at least 0.01.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be JPEG, PNG, JPG, or GIF.',
            'image.max' => 'The image cannot exceed 2MB.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'custom_price.numeric' => 'Custom price must be a valid number.',
        ];
    }
}
