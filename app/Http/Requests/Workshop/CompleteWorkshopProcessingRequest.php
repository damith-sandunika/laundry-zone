<?php

namespace App\Http\Requests\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class CompleteWorkshopProcessingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cubby_id'       => 'nullable|exists:cubbies,id',
            'payment_method' => 'nullable|string',
            'amount_paid'    => 'nullable|numeric|min:0',
        ];
    }
}
