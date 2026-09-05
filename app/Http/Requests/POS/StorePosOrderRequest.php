<?php

namespace App\Http\Requests\POS;

use Illuminate\Foundation\Http\FormRequest;

class StorePosOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => 'nullable|exists:customers,id',
            'new_customer_name' => 'nullable|string|max:255',
            'new_customer_phone' => 'nullable|string|max:255',
            'cart' => 'required|array|min:1',
            'payment_mode' => 'nullable|in:pay_now,pay_on_pickup',
            'due_date' => 'required|string',
        ];
    }
}
