<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class DispatchDeliveryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'delivery_method' => 'required|in:rider,pickme_flash',
            'rider_id'        => 'required_if:delivery_method,rider|nullable|exists:users,id',
            'pickme_cost'     => 'required_if:delivery_method,pickme_flash|nullable|numeric|min:0',
            'payment_method'  => 'nullable|in:cash,card,bank_transfer,credit',
        ];
    }
}
