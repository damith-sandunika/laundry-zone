<?php

namespace App\Actions\Customer;

use App\Models\Customer;

class StoreCustomerAction
{
    public function execute(array $data): Customer
    {
        $customerType = $data['customer_type'] ?? null;
        if (!$customerType) {
            $customerType = !empty($data['is_corporate']) ? 'corporate' : 'walkin';
        }
        $isCorporate = ($customerType === 'corporate');

        return Customer::create([
            'title' => $data['title'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'nic' => $data['nic'] ?? null,
            'customer_mark' => $data['customer_mark'] ?? null,
            'customer_type' => $customerType,
            'is_corporate' => $isCorporate,
            'is_active' => true,
        ]);
    }
}
