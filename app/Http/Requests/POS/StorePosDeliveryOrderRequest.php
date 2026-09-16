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
            'delivery_date'    => 'nullable|string',
            'notes'            => 'nullable|string|max:500',
            'cart'             => 'nullable|array',
            'payment_mode'     => 'nullable|in:pay_now,pay_on_pickup',
            'payment_method'   => 'nullable|string|in:cash,card,bank',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'advance_amount'   => 'nullable|numeric|min:0',
            'customer_package_id' => 'nullable|integer',
        ];
    }
}
