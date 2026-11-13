<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Package;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\TurnaroundType;
use App\Models\Extra;
use App\Models\PickupTimePeriod;
use App\Models\DeliveryRange;

class CustomerSiteService
{
    public function getHomeData(): array
    {
        $branches = Branch::where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'workshop');
                if (config('app.delivery_enabled')) {
                    $q->orWhere('is_delivery_enabled', true);
                }
            })
            ->get();

        $categories = ServiceCategory::with([
            'services' => function ($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'children' => function ($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            },
            'children.services' => function ($q) {
                $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index')->get();

        $packages = Package::with('services.serviceType')->where('is_active', true)->get();
        $turnaroundTypes = TurnaroundType::all();
        $extras = Extra::where('is_active', true)->get();
        $pickupTimePeriods = PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();
        $minimumOrderLimit = Setting::get('minimum_delivery_cost', 0);
        $shopLat = Setting::get('shop_latitude', '6.9271');
        $shopLng = Setting::get('shop_longitude', '79.8612');
        $deliveryRanges = DeliveryRange::orderBy('min_distance_km')->get();

        $workshops = Branch::where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'workshop');
                if (config('app.delivery_enabled')) {
                    $q->orWhere('is_delivery_enabled', true);
                }
            })
            ->get();

        if ($workshops->isEmpty()) {
            $fallback = Branch::where('is_active', true)->first();
            if ($fallback) {
                $workshops = collect([$fallback]);
            }
        }

        $paymentMethods = $this->getPaymentMethods();
        $isCentralized = Setting::get('centralized_management', '1') == '1';

        return compact(
            'branches', 'categories', 'packages', 'turnaroundTypes', 'extras',
            'pickupTimePeriods', 'minimumOrderLimit', 'shopLat', 'shopLng',
            'deliveryRanges', 'workshops', 'paymentMethods', 'isCentralized'
        );
    }

    public function getBookingData(): array
    {
        $paymentMethods = $this->getPaymentMethods();
        $isCentralized = Setting::get('centralized_management', '1') == '1';

        $categories = collect();
        $pickupTimePeriods = collect();
        $turnaroundTypes = collect();
        $extras = collect();

        if ($isCentralized) {
            $categories = ServiceCategory::with([
                'children' => function ($q) {
                    $q->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index');
                },
                'children.services' => function ($q) {
                    $q->where('is_visible_on_website', true)->orderBy('order_index');
                },
                'services' => function ($q) {
                    $q->where('is_visible_on_website', true)->orderBy('order_index');
                }
            ])->whereNull('parent_id')->where('is_active', true)->where('is_visible_on_website', true)->orderBy('order_index')->get();

            $pickupTimePeriods = PickupTimePeriod::where('is_active', true)->orderBy('start_time')->get();
            $turnaroundTypes = TurnaroundType::all();
            $extras = Extra::where('is_active', true)->get();
        }

        $minimumOrderLimit = Setting::get('minimum_delivery_cost', 0);
        $shopLat = Setting::get('shop_latitude', '6.9271');
        $shopLng = Setting::get('shop_longitude', '79.8612');
        $deliveryRanges = DeliveryRange::orderBy('min_distance_km')->get();

        $workshops = Branch::where('is_active', true)
            ->where(function ($q) {
                $q->where('type', 'workshop');
                if (config('app.delivery_enabled')) {
                    $q->orWhere('is_delivery_enabled', true);
                }
            })
            ->get();

        if ($workshops->isEmpty()) {
            $fallback = Branch::where('is_active', true)->first();
            if ($fallback) {
                $workshops = collect([$fallback]);
            }
        }
        $workshop = $workshops->first();

        return compact(
            'paymentMethods', 'categories', 'pickupTimePeriods', 'minimumOrderLimit',
            'deliveryRanges', 'workshop', 'workshops', 'turnaroundTypes', 'extras',
            'shopLat', 'shopLng', 'isCentralized'
        );
    }

    public function getBranchServicesData(Branch $branch): array
    {
        $branchId = $branch->id;
        $isCentralized = Setting::get('centralized_management', '1') == '1';

        $categories = ServiceCategory::with([
            'children' => function ($q) use ($branchId, $isCentralized) {
                $q->where('is_active', true)->where('is_visible_on_website', true);
                if (!$isCentralized) {
                    $q->where(function ($sq) use ($branchId) {
                        $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
                $q->orderBy('order_index')->with(['services' => function ($sq) use ($branchId, $isCentralized) {
                    $sq->where('is_visible_on_website', true);
                    if (!$isCentralized) {
                        $sq->where(function ($ssq) use ($branchId) {
                            $ssq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                        });
                    }
                    $sq->orderBy('order_index');
                }]);
            },
            'services' => function ($q) use ($branchId, $isCentralized) {
                $q->where('is_visible_on_website', true);
                if (!$isCentralized) {
                    $q->where(function ($sq) use ($branchId) {
                        $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
                $q->orderBy('order_index');
            }
        ])->whereNull('parent_id')->where('is_active', true)->where('is_visible_on_website', true)
        ->where(function ($q) use ($branchId, $isCentralized) {
            if (!$isCentralized) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }
        })->orderBy('order_index')->get();

        $pickupTimePeriods = PickupTimePeriod::where('is_active', true)
            ->where(function ($q) use ($branchId, $isCentralized) {
                if (!$isCentralized) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })->orderBy('start_time')->get();

        $turnaroundTypes = TurnaroundType::where(function ($q) use ($branchId, $isCentralized) {
            if (!$isCentralized) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            }
        })->get();

        $extras = Extra::where('is_active', true)
            ->where(function ($q) use ($branchId, $isCentralized) {
                if (!$isCentralized) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })->get();

        return compact('categories', 'pickupTimePeriods', 'turnaroundTypes', 'extras');
    }

    public function getPaymentMethods()
    {
        $methods = collect();
        if (Setting::get('payment_bank_transfer_active', '1') == '1') {
            $methods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (Setting::get('payment_online_active', '0') == '1') {
            $methods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }
        if (Setting::get('payment_cod_active', '1') == '1') {
            $methods->push((object)['id' => 'cash_on_delivery', 'name' => 'Cash on Delivery', 'type' => 'cash_on_delivery']);
        }
        return $methods;
    }
}
