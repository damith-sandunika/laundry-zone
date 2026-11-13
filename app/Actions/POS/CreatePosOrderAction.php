<?php

namespace App\Actions\POS;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePosOrderAction
{
    protected CalculatePosCartAction $cartCalculator;
    protected PersistPosOrderAction $orderPersister;

    public function __construct(
        ?CalculatePosCartAction $cartCalculator = null,
        ?PersistPosOrderAction $orderPersister = null
    ) {
        $this->cartCalculator = $cartCalculator ?? new CalculatePosCartAction();
        $this->orderPersister = $orderPersister ?? new PersistPosOrderAction();
    }

    public function execute(User $user, array $data): array
    {
        $branchId = $user->branch_id ?? Branch::first()->id;
        $packageId = $data['customer_package_id'] ?? null;

        $cartData = $this->cartCalculator->execute($data['cart'], $packageId);
        $subtotal = $cartData['subtotal'];

        $discountType = $data['discount_type'] ?? 'percentage';
        $discountAmount = 0;
        $discountPercentage = 0;

        if ($discountType === 'fixed') {
            $fixedVal = (float) ($data['discount_value'] ?? ($data['discount_amount'] ?? 0));
            $discountAmount = max(0, min($subtotal, $fixedVal));
            $discountPercentage = $subtotal > 0 ? round(($discountAmount / $subtotal) * 100, 2) : 0;
        } else {
            $discountPercentage = max(0, min(100, (float) ($data['discount_value'] ?? ($data['discount_percentage'] ?? 0))));
            $discountAmount = $subtotal * ($discountPercentage / 100);
        }

        $total = max(0, $subtotal - $discountAmount);
        $paymentMode = $data['payment_mode'] ?? 'pay_on_pickup';

        $paidAmount = 0;
        if ($paymentMode === 'pay_now') {
            if (isset($data['advance_amount']) && $data['advance_amount'] !== null) {
                $paidAmount = (float) $data['advance_amount'];
            } else {
                $paidAmount = $total;
            }
        }

        return DB::transaction(function () use (
            $user, $data, $branchId, $subtotal, $discountAmount, $discountPercentage,
            $total, $paidAmount, $paymentMode, $cartData
        ) {
            $customerId = $data['customer_id'] ?? null;
            if (!$customerId) {
                $customer = Customer::withoutGlobalScopes()->firstOrCreate(
                    ['phone' => '0000000000'],
                    ['name' => 'Walk-in Customer', 'branch_id' => $branchId]
                );
                $customerId = $customer->id;
            }

            if ($discountPercentage > 0 && $customerId) {
                Customer::where('id', $customerId)->update(['default_discount' => $discountPercentage]);
            }

            $branchProfit = 0;
            $workshopProfit = 0;
            $branch = Branch::find($branchId);
            if ($branch && $branch->branch_profit_percentage > 0) {
                $branchProfit = $subtotal * ($branch->branch_profit_percentage / 100);
                $workshopProfit = $subtotal - $branchProfit;
            }

            $orderParams = [
                'subtotal' => $subtotal,
                'discountAmount' => $discountAmount,
                'total' => $total,
                'paidAmount' => $paidAmount,
                'paymentMode' => $paymentMode,
                'branchProfit' => $branchProfit,
                'workshopProfit' => $workshopProfit,
                'customerId' => $customerId,
            ];

            return $this->orderPersister->execute($user, $branchId, $orderParams, $cartData, $data);
        });
    }
}
