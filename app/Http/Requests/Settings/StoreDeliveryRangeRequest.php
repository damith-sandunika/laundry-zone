<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRangeRequest extends FormRequest
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
            'min_distance_km' => 'required|numeric|min:0',
            'max_distance_km' => 'required|numeric|gt:min_distance_km',
            'cost' => 'required|numeric|min:0',
        ];
    }
}
