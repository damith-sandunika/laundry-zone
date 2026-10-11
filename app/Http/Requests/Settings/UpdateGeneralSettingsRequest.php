<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneralSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    
    public function authorize(): bool { return true; }
    public function rules(): array {
        $user = auth()->user();
        if ($user && $user->role === 'super_admin') {
            return [
                'company_name' => 'nullable|string|max:255',
                'auto_close_time' => 'nullable|date_format:H:i',
                'centralized_management' => 'nullable|boolean',
                'allow_branch_admins_delete_orders' => 'nullable|boolean',
                'site_landing_page' => 'nullable|string|max:255',
            ];
        }
        return [
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'logo_size' => 'nullable|integer|min:20|max:300',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }
}
