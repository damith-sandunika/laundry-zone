<?php

namespace App\Http\Requests\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class WorkshopBulkStatusUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'dispatch_ids' => 'required|array',
            'dispatch_ids.*' => 'exists:workshop_dispatches,id',
            'status' => 'required|string|in:processing,completed_at_workshop,returned_to_branch',
        ];
    }
}
