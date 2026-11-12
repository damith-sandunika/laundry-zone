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
            'payment_method' => 'nullable|string|in:cash,card,bank',
            'due_date' => 'required|string',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'customer_package_id' => 'nullable|integer',
        ];
    }
}
