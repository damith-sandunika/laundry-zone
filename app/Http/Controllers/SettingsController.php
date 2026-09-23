<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Cubby;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Package;
use App\Models\PackageService;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    protected function authorizeConfigManagement()
    {
        $isCentralized = \App\Models\Setting::get('centralized_management', '1') == '1';
        $isGlobalManager = auth()->user()->canManageGlobalConfig();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $canManage = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
        
        if (!$canManage) {
            abort(403, 'Unauthorized config management mode.');
        }
    }
    public function servicesIndex()
    {
        $services = ServiceType::all();
        $inventoryItems = InventoryItem::where('branch_id', $this->getActiveBranchId())->get();

        return view('services.index', compact('services', 'inventoryItems'));
    }

    public function servicesManager()
    {
        $this->authorizeConfigManagement();

        $branchId = $this->getActiveBranchId();
        $isGlobalMode = \App\Models\Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();

        $serviceCategories = ServiceCategory::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->with(['children.services' => function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->where(function($sq) use ($branchId) {
                        $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
                $q->orderBy('order_index');
            }, 'services' => function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->where(function($sq) use ($branchId) {
                        $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
                $q->orderBy('order_index');
            }])->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');
        $services = ServiceType::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->with(['inventoryItems', 'category'])->orderBy('order_index')->get();

        return view('settings.services', compact('serviceCategories', 'mainCategories', 'services'));
    }

    public function index()
    {
        $branchId = $this->getActiveBranchId();
        $branch = Branch::find($branchId);
        $isGlobalMode = \App\Models\Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();
        
        $serviceCategories = ServiceCategory::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->with(['children.services' => function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->where(function($sq) use ($branchId) {
                        $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
                $q->orderBy('order_index');
            }, 'services' => function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->where(function($sq) use ($branchId) {
                        $sq->whereNull('branch_id')->orWhere('branch_id', $branchId);
                    });
                }
                $q->orderBy('order_index');
            }])->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');

        $services = ServiceType::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->with(['inventoryItems', 'category'])->orderBy('order_index')->get();

        $turnaroundTypes = \App\Models\TurnaroundType::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->orderBy('is_default', 'desc')->orderBy('name')->get();
        
        $extras = \App\Models\Extra::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->orderBy('name')->get();

        $cubbies = Cubby::where('branch_id', $branchId)->get();
        $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();
        $packages = Package::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->with('services.serviceType')->get();
        $deliveryRanges = \App\Models\DeliveryRange::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })->get();
        $paymentMethods = collect();
        if (\App\Models\Setting::get('payment_bank_transfer_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (\App\Models\Setting::get('payment_online_active', '0') == '1') {
            $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }
        $pickupTimePeriods = \App\Models\PickupTimePeriod::where(function($q) use ($branchId, $isGlobalMode) {
                if (!$isGlobalMode) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                }
            })
            ->orderBy('start_time')->get();
        $workshops = Branch::workshops()->get();
        $minimumDeliveryCost = \App\Models\Setting::get('minimum_delivery_cost', 0);

        $paymentMethodsList = [
            (object) [
                'id' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'type' => 'bank_transfer',
                'is_active' => \App\Models\Setting::get('payment_bank_transfer_active', '1') == '1',
            ],
            (object) [
                'id' => 'online_payment',
                'name' => 'Online Payment',
                'type' => 'card_transfer',
                'is_active' => \App\Models\Setting::get('payment_online_active', '0') == '1',
            ],
        ];
        
        $bankDetails = [
            'bank_name' => \App\Models\Setting::get('bank_name', ''),
            'bank_account_name' => \App\Models\Setting::get('bank_account_name', ''),
            'bank_account_number' => \App\Models\Setting::get('bank_account_number', ''),
            'bank_branch' => \App\Models\Setting::get('bank_branch', ''),
        ];

        $gatewayDetails = [
            'gateway_merchant_id' => \App\Models\Setting::get('gateway_merchant_id', ''),
            'gateway_secret_key' => \App\Models\Setting::get('gateway_secret_key', ''),
        ];

        return view('settings.index', compact('branch', 'serviceCategories', 'mainCategories', 'services', 'cubbies', 'inventoryItems', 'packages', 'paymentMethods', 'deliveryRanges', 'pickupTimePeriods', 'workshops', 'minimumDeliveryCost', 'paymentMethodsList', 'bankDetails', 'gatewayDetails', 'turnaroundTypes', 'extras'));
    }

    public function updateGeneral(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->role === 'super_admin';

        if ($isSuperAdmin) {
            $request->validate([
                'company_name' => 'nullable|string|max:255',
                'auto_close_time' => 'nullable|date_format:H:i',
                'centralized_management' => 'nullable|boolean',
                'site_landing_page' => 'nullable|string|max:255',
            ]);
            
            \App\Models\Setting::set('centralized_management', $request->has('centralized_management') ? '1' : '0');
            \App\Models\Setting::set('site_landing_page', $request->input('site_landing_page', ''));

            if ($request->has('company_name')) {
                \App\Models\Setting::set('company_name', $request->company_name);
            }
            if ($request->has('auto_close_time')) {
                Branch::query()->update(['auto_close_time' => $request->auto_close_time]);
            }
        } else {
            $branchId = $this->getActiveBranchId();
            $branch = Branch::find($branchId);

            $request->validate([
                'name' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'phone' => 'nullable|string|max:50',
                'whatsapp_number' => 'nullable|string|max:50',
                'tagline' => 'nullable|string|max:255',
                'logo_size' => 'nullable|integer|min:20|max:300',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            ]);

            $data = $request->only(['name', 'address', 'latitude', 'longitude', 'phone', 'whatsapp_number', 'tagline', 'logo_size']);

            if ($request->hasFile('logo')) {
                $data['logo_path'] = app(\App\Actions\UploadImageAction::class)->execute($request->file('logo'), 'logos', 'logo');
            }

            if ($branch) {
                $branch->update($data);
            }
        }

        return redirect()->back()->with('success', 'General settings saved.');
    }

    public function updateNotifications(Request $request)
    {
        if (!\Illuminate\Support\Facades\Gate::allows('is-super-admin')) {
            abort(403);
        }

        \App\Models\Setting::set('is_sms_enabled', $request->has('is_sms_enabled') ? 'true' : 'false');
        \App\Models\Setting::set('is_whatsapp_enabled', $request->has('is_whatsapp_enabled') ? 'true' : 'false');
        \App\Models\Setting::set('is_email_enabled', $request->has('is_email_enabled') ? 'true' : 'false');

        return redirect()->back()->with('success', 'Notification settings saved successfully.');
    }

    public function storeService(Request $request)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item,per_sqft',
            'inventory' => 'nullable|array',
        ]);

        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        $service = ServiceType::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'item_code' => $request->item_code,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'price_type' => $request->price_type,
            'icon' => $request->icon ?? '🧺',
            'color' => $request->color ?? 'blue',
            'is_active' => true,
            'is_visible_on_website' => $request->has('is_visible_on_website'),
        ]);

        return redirect()->back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, ServiceType $service)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'item_code' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item,per_sqft',
            'is_active' => 'boolean',
            'inventory' => 'nullable|array',
        ]);
        $updateData = $request->only(['name', 'item_code', 'category_id', 'base_price', 'price_type', 'is_active', 'icon', 'color']);
        $updateData['is_visible_on_website'] = $request->has('is_visible_on_website');
        $service->update($updateData);

        return redirect()->back()->with('success', 'Service updated.');
    }

    public function destroyService(ServiceType $service)
    {
        $this->authorizeConfigManagement();

        $service->delete();

        return redirect()->back()->with('success', 'Service deleted.');
    }

    public function storeServiceCategory(Request $request)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:service_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = app(\App\Actions\UploadImageAction::class)->execute($request->file('image'), 'categories', 'cat');
        }

        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        ServiceCategory::create([
            'branch_id' => $branchId,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'is_active' => true,
            'image_path' => $imagePath,
            'is_visible_on_website' => $request->has('is_visible_on_website'),
        ]);

        return redirect()->back()->with('success', 'Service Category created.');
    }

    public function updateServiceCategory(Request $request, ServiceCategory $category)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:service_categories,id',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $data = $request->only(['name', 'parent_id', 'is_active']);
        $data['is_visible_on_website'] = $request->has('is_visible_on_website');

        if ($request->hasFile('image')) {
            $data['image_path'] = app(\App\Actions\UploadImageAction::class)->execute($request->file('image'), 'categories', 'cat');
        }

        $category->update($data);

        return redirect()->back()->with('success', 'Service Category updated.');
    }

    public function destroyServiceCategory(ServiceCategory $category)
    {
        $this->authorizeConfigManagement();

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted.');
    }

    public function reorderCategories(Request $request)
    {
        $this->authorizeConfigManagement();
        $request->validate(['ordered_ids' => 'required|array']);
        foreach ($request->ordered_ids as $index => $id) {
            ServiceCategory::where('id', $id)->update(['order_index' => $index]);
        }
        return response()->json(['success' => true]);
    }

    public function reorderServices(Request $request)
    {
        $this->authorizeConfigManagement();
        $request->validate([
            'ordered_ids' => 'required|array',
            'category_id' => 'nullable'
        ]);
        $categoryId = $request->category_id;
        if ($categoryId === 'null') {
            $categoryId = null;
        }
        
        foreach ($request->ordered_ids as $index => $id) {
            if ($id) {
                ServiceType::where('id', $id)->update([
                    'order_index' => $index,
                    'category_id' => $categoryId
                ]);
            }
        }
        return response()->json(['success' => true]);
    }

    public function storeCubby(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $branchId = $this->getActiveBranchId();
        Cubby::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'status' => 'empty',
        ]);

        return redirect()->back()->with('success', 'Storage location added.');
    }

    public function updateCubby(Request $request, Cubby $cubby)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $cubby->update(['name' => $request->name]);

        return redirect()->back()->with('success', 'Storage location updated.');
    }

    public function destroyCubby(Cubby $cubby)
    {
        $cubby->delete();

        return redirect()->back()->with('success', 'Storage location deleted.');
    }

    public function storeInventory(Request $request)
    {
        $branchId = $this->getActiveBranchId();
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ]);
        InventoryItem::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'unit' => $request->unit,
            'current_stock' => $request->current_stock,
            'min_stock' => $request->min_stock ?? 0,
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            'location' => $request->location,
        ]);

        return redirect()->back()->with('success', 'Inventory item added.');
    }

    public function stockIn(Request $request, InventoryItem $item)
    {
        $request->validate(['quantity' => 'required|numeric|min:0.01']);
        $item->increment('quantity', $request->quantity);
        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'type' => 'stock_in',
            'quantity' => $request->quantity,
            'reference' => $request->reference ?? 'Manual stock-in',
            'notes' => $request->notes ?? null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Stock updated.');
    }

    public function whatsappStatus()
    {
        $branchId = $this->getActiveBranchId();
        try {
            $response = Http::timeout(5)->get("http://127.0.0.1:3005/api/whatsapp/status/{$branchId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'qr' => null, 'error' => 'Service offline']);
        }
    }

    public function whatsappLogout()
    {
        $branchId = $this->getActiveBranchId();
        try {
            $response = Http::post("http://127.0.0.1:3005/api/whatsapp/logout/{$branchId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }

    public function whatsappReset()
    {
        $branchId = $this->getActiveBranchId();
        try {
            $response = Http::post("http://127.0.0.1:3005/api/whatsapp/start/{$branchId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }

    public function storePackage(Request $request)
    {
        $this->authorizeConfigManagement();
        
        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'payment_type' => 'required|in:prepaid,postpaid',
            'services' => 'required|array',
            'services.*' => 'nullable|numeric|min:0',
        ]);

        $package = Package::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'price' => $request->price,
            'payment_type' => $request->payment_type,
            'is_active' => true,
        ]);

        foreach ($request->services as $serviceId => $limit) {
            if ($limit > 0) {
                PackageService::create([
                    'package_id' => $package->id,
                    'service_type_id' => $serviceId,
                    'quantity_limit' => $limit,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Package created successfully.');
    }

    public function updatePackage(Request $request, Package $package)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'payment_type' => 'required|in:prepaid,postpaid',
            'services' => 'required|array',
            'services.*' => 'nullable|numeric|min:0',
        ]);

        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'payment_type' => $request->payment_type,
        ]);

        // Re-create services
        PackageService::where('package_id', $package->id)->delete();
        foreach ($request->services as $serviceId => $limit) {
            if ($limit > 0) {
                PackageService::create([
                    'package_id' => $package->id,
                    'service_type_id' => $serviceId,
                    'quantity_limit' => $limit,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Package updated successfully.');
    }

    public function destroyPackage(Package $package)
    {
        $this->authorizeConfigManagement();
        
        $package->delete();

        return redirect()->back()->with('success', 'Package deleted successfully.');
    }

    public function updateDeliverySettings(Request $request)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'minimum_delivery_cost' => 'required|numeric|min:0',
            'workshops' => 'nullable|array',
            'workshops.*.latitude' => 'nullable|numeric',
            'workshops.*.longitude' => 'nullable|numeric',
        ]);

        \App\Models\Setting::set('minimum_delivery_cost', $request->minimum_delivery_cost);

        if ($request->has('workshops')) {
            foreach ($request->workshops as $workshopId => $data) {
                $workshop = Branch::workshops()->find($workshopId);
                if ($workshop) {
                    $workshop->update([
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Delivery settings updated successfully.');
    }

    public function storeDeliveryRange(Request $request)
    {
        $this->authorizeConfigManagement();

        $request->validate([
            'min_distance_km' => 'required|numeric|min:0',
            'max_distance_km' => 'required|numeric|gt:min_distance_km',
            'cost' => 'required|numeric|min:0',
        ]);

        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();
        \App\Models\DeliveryRange::create([
            'branch_id' => $branchId,
            'min_distance_km' => $request->min_distance_km,
            'max_distance_km' => $request->max_distance_km,
            'cost' => $request->cost,
        ]);

        return redirect()->back()->with('success', 'Delivery Range added successfully.');
    }

    public function deleteDeliveryRange(\App\Models\DeliveryRange $deliveryRange)
    {
        $this->authorizeConfigManagement();
        $deliveryRange->delete();
        return redirect()->back()->with('success', 'Delivery Range deleted successfully.');
    }

    public function storePickupTimePeriod(Request $request)
    {
        $this->authorizeConfigManagement();
        $request->validate([
            'label' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();
        \App\Models\PickupTimePeriod::create([
            'branch_id' => $branchId,
            'label' => $request->label,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Pickup time period added successfully.');
    }

    public function deletePickupTimePeriod(\App\Models\PickupTimePeriod $pickupTimePeriod)
    {
        $this->authorizeConfigManagement();
        $pickupTimePeriod->delete();
        return redirect()->back()->with('success', 'Pickup time period deleted successfully.');
    }

    // Extras CRUD
    public function storeExtra(Request $request)
    {
        $this->authorizeConfigManagement();
        $request->validate([
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:per_piece,per_kg,per_sqft',
            'price' => 'required|numeric|min:0',
        ]);

        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        \App\Models\Extra::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'price_type' => $request->price_type,
            'price' => $request->price,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Extra added successfully.');
    }

    public function updateExtra(Request $request, \App\Models\Extra $extra)
    {
        $this->authorizeConfigManagement();
        $request->validate([
            'name' => 'required|string|max:255',
            'price_type' => 'required|in:per_piece,per_kg,per_sqft',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $extra->update($request->only(['name', 'price_type', 'price', 'is_active']));

        return redirect()->back()->with('success', 'Extra updated successfully.');
    }

    public function destroyExtra(\App\Models\Extra $extra)
    {
        $this->authorizeConfigManagement();
        $extra->delete();
        return redirect()->back()->with('success', 'Extra deleted successfully.');
    }

    // Turnaround Types CRUD
    public function storeTurnaroundType(Request $request)
    {
        $this->authorizeConfigManagement();
        $request->validate([
            'name' => 'required|string|max:255',
            'markup_type' => 'required|in:percentage,flat',
            'markup_value' => 'required|numeric|min:0',
            'is_default' => 'boolean',
        ]);

        if ($request->has('is_default')) {
            \App\Models\TurnaroundType::query()->update(['is_default' => false]);
        }

        $branchId = \App\Models\Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        \App\Models\TurnaroundType::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'markup_type' => $request->markup_type,
            'markup_value' => $request->markup_value,
            'is_default' => $request->has('is_default'),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Turnaround type added successfully.');
    }

    public function updateTurnaroundType(Request $request, \App\Models\TurnaroundType $turnaroundType)
    {
        $this->authorizeConfigManagement();
        $request->validate([
            'name' => 'required|string|max:255',
            'markup_type' => 'required|in:percentage,flat',
            'markup_value' => 'required|numeric|min:0',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($request->has('is_default') && $request->is_default) {
            \App\Models\TurnaroundType::where('id', '!=', $turnaroundType->id)
                ->update(['is_default' => false]);
        }

        $turnaroundType->update([
            'name' => $request->name,
            'markup_type' => $request->markup_type,
            'markup_value' => $request->markup_value,
            'is_default' => $request->has('is_default'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'Turnaround type updated successfully.');
    }

    public function destroyTurnaroundType(\App\Models\TurnaroundType $turnaroundType)
    {
        $this->authorizeConfigManagement();
        if (strtolower($turnaroundType->name) === 'standard') {
            return redirect()->back()->with('error', 'The Standard turnaround type cannot be deleted.');
        }
        $turnaroundType->delete();
        return redirect()->back()->with('success', 'Turnaround type deleted successfully.');
    }
}

