<?php

namespace App\Http\Requests\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class BulkDispatchToWorkshopRequest extends FormRequest
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
            'workshop_branch_id' => 'required|exists:branches,id',
            'dispatch_notes' => 'nullable|string',
        ];
    }
}
