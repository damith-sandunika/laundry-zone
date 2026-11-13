<?php

namespace App\Actions\Customer;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\UploadedFile;

class ProcessCustomerPaymentAction
{
    public function execute(Order $order, float $amount, string $paymentMethod, ?UploadedFile $file = null): void
    {
        $receiptPath = $order->receipt_path;
        if ($file && $file->isValid()) {
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'laundry_zone/receipts',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $receiptPath = $result['secure_url'];
                } catch (\Exception $e) {
                    $receiptPath = $file->store('receipts', 'public');
                }
            } else {
                $receiptPath = $file->store('receipts', 'public');
            }
        }

        if ($paymentMethod === 'gateway') {
            $order->update(['payment_method' => 'card']);
            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => 'card',
                'status' => 'pending',
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
            ]);
        } else {
            $order->update([
                'receipt_path' => $receiptPath,
                'payment_method' => 'bank',
            ]);
            PaymentTransaction::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'payment_method' => 'bank',
                'receipt_path' => $file ? $receiptPath : null,
                'status' => 'pending',
            ]);
        }

        $order->refresh();
        if ($order->paid_amount >= $order->total && $order->total > 0) {
            $order->update(['payment_status' => 'paid']);
        } elseif ($order->paid_amount > 0) {
            $order->update(['payment_status' => 'partial']);
        } else {
            $order->update(['payment_status' => 'unpaid']);
        }
    }
}

