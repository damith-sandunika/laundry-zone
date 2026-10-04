<?php

namespace App\Http\Requests\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class WorkshopHandoverRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'payment_method'     => 'nullable|string',
            'amount_paid'        => 'nullable|numeric|min:0',
            'refund_overpayment' => 'nullable|boolean',
        ];
    }
}
