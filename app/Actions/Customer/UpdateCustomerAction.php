<?php

namespace App\Actions\Customer;

use App\Models\Customer;

class UpdateCustomerAction
{
    public function execute(Customer $customer, array $data): Customer
    {
        $payload = [
            'title' => $data['title'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'nic' => $data['nic'] ?? null,
            'customer_mark' => $data['customer_mark'] ?? null,
        ];

        if (!empty($data['customer_type'])) {
            $payload['customer_type'] = $data['customer_type'];
            $payload['is_corporate'] = ($data['customer_type'] === 'corporate');
        } elseif (array_key_exists('is_corporate', $data)) {
            $payload['is_corporate'] = (bool) $data['is_corporate'];
            $payload['customer_type'] = $payload['is_corporate'] ? 'corporate' : 'walkin';
        }

        if (!empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }

        $customer->update($payload);
        return $customer;
    }
}

