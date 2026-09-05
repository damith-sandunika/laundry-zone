<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StorePosDeliveryOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id'      => 'required|exists:customers,id',
            'pickup_address'   => 'required|string|max:500',
            'delivery_address' => 'nullable|string|max:500',
            'pickup_date'      => 'required|string',
            'notes'            => 'nullable|string|max:500',
            'cart'             => 'nullable|array',
            'payment_mode'     => 'nullable|in:pay_now,pay_on_pickup',
        ];
    }
}
