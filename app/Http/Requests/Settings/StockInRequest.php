<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity'  => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:255',
            'notes'     => 'nullable|string|max:1000',
        ];
    }
}
