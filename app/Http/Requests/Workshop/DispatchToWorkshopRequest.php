<?php

namespace App\Http\Requests\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class DispatchToWorkshopRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'dispatch_notes' => 'nullable|string|max:1000',
            'workshop_branch_id' => 'nullable|exists:branches,id',
        ];
    }
}
