<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliverySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'minimum_delivery_cost'  => 'required|numeric|min:0',
            'workshops'              => 'nullable|array',
            'workshops.*.latitude'   => 'nullable|numeric',
            'workshops.*.longitude'  => 'nullable|numeric',
        ];
    }
}
