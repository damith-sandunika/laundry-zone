<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item,per_sqft,per_pair',
            'is_active' => 'boolean',
            'inventory' => 'nullable|array',
            'is_visible_on_website' => 'nullable',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
        ];
    }
}

