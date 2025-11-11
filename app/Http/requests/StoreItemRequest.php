<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'subcategory_id' => 'required|exists:subcategories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sizes' => 'required|array',
            'sizes.*.size' => 'required|string|max:50',
            'sizes.*.price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The item name is required.',
            'subcategory_id.required' => 'Please select a subcategory.',
            'subcategory_id.exists' => 'The selected subcategory is invalid.',
            'image.image' => 'The file must be an image.',
            'sizes.required' => 'At least one size is required.',
            'sizes.*.size.required' => 'The size field is required.',
            'sizes.*.price.required' => 'The price field is required.',
            'sizes.*.price.numeric' => 'The price must be a number.',
            'sizes.*.price.min' => 'The price must be at least 0.',
        ];
    }
}