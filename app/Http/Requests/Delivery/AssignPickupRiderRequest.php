<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class AssignPickupRiderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'delivery_method' => 'nullable|string|in:in_house,pickme_flash',
            'rider_id' => 'required_if:delivery_method,in_house',
        ];
    }
}
