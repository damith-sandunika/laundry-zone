<?php

namespace App\Http\Requests\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Authorization is handled by controller middleware/roles
    }

    public function rules()
    {
        return [
            'reason' => 'nullable|string|max:255',
        ];
    }
}
