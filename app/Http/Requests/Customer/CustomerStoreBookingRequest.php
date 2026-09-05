<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreBookingRequest extends FormRequest
{
    public function authorize()
    {
        return auth('customer')->check();
    }

    public function rules()
    {
        return [
            'pickup_address' => 'required|string|max:255',
            'map_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time_period_id' => 'required|exists:pickup_time_periods,id',
            'item_description' => 'nullable|string',
            'notes' => 'nullable|string',

            'delivery_preference' => 'required|in:deliver_to_address,pickup_from_branch',
            'same_as_pickup' => 'nullable|boolean',
            'delivery_lat' => 'nullable|numeric',
            'delivery_lng' => 'nullable|numeric',
            'delivery_address' => 'nullable|string|max:255',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'estimated_delivery_fee' => 'nullable|numeric',
            
            // Allow cart_json
            'cart_json' => 'nullable|string',
        ];
    }
}
