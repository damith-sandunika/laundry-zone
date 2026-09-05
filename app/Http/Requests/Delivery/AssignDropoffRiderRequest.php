<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class AssignDropoffRiderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'rider_id' => 'required',
            'order_id' => 'required|exists:orders,id',
            'pickme_flash_cost' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
        ];
    }
}
