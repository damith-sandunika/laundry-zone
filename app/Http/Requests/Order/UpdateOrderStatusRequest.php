<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status'         => 'required|in:received,dispatched_to_workshop,processing,returned_from_workshop,done,delivered,completed,cancelled',
            'cubby_id'       => 'nullable|exists:cubbies,id',
            'payment_method' => 'nullable|in:cash,card,bank,bank_transfer,credit',
        ];
    }
}
