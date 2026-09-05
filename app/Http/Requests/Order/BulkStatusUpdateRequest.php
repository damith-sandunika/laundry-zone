<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class BulkStatusUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:received,dispatched_to_workshop,processing,returned_from_workshop,done,delivered,completed,cancelled',
            'cubby_id' => 'nullable|exists:cubbies,id',
        ];
    }
}
