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
    public function servicesIndex()
    {
        $services = ServiceType::all();
        $inventoryItems = InventoryItem::where('branch_id', auth()->user()->branch_id ?? 1)->get();

        return view('services.index', compact('services', 'inventoryItems'));
    }

    public function index()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $branch = Branch::find($branchId);
        $serviceCategories = ServiceCategory::with('services')->where('branch_id', $branchId)->get();
        $services = ServiceType::with(['inventoryItems', 'category'])->where('branch_id', $branchId)->get();
        $cubbies = Cubby::where('branch_id', $branchId)->get();
        $inventoryItems = InventoryItem::where('branch_id', $branchId)->get();
        $packages = Package::with('services.serviceType')->where('branch_id', $branchId)->get();
        $deliveryRanges = \App\Models\DeliveryRange::where('branch_id', $branchId)->orWhereNull('branch_id')->get();
        $paymentMethods = collect();
        if (\App\Models\Setting::get('payment_bank_transfer_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'type' => 'bank_transfer']);
        }
        if (\App\Models\Setting::get('payment_online_active', '0') == '1') {
            $paymentMethods->push((object)['id' => 'online_payment', 'name' => 'Online Payment', 'type' => 'card_transfer']);
        }
        $pickupTimePeriods = \App\Models\PickupTimePeriod::orderBy('start_time')->get();
        if (\App\Models\Setting::get('payment_cod_active', '1') == '1') {
            $paymentMethods->push((object)['id' => 'cash_on_delivery', 'name' => 'Cash on Delivery', 'type' => 'cash_on_delivery']);
        }
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
            (object) [
                'id' => 'cash_on_delivery',
                'name' => 'Cash on Delivery',
                'type' => 'cash_on_delivery',
                'is_active' => \App\Models\Setting::get('payment_cod_active', '1') == '1',
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

        return view('settings.index', compact('branch', 'serviceCategories', 'services', 'cubbies', 'inventoryItems', 'packages', 'paymentMethods', 'deliveryRanges', 'pickupTimePeriods', 'workshops', 'minimumDeliveryCost', 'paymentMethodsList', 'bankDetails', 'gatewayDetails'));
    }

    public function updateGeneral(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $branch = Branch::find($branchId);

        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'phone' => 'nullable|string|max:50',
            'whatsapp_number' => 'nullable|string|max:50',
            'tagline' => 'nullable|string|max:255',
            'logo_size' => 'nullable|integer|min:20|max:300',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'auto_close_time' => 'nullable|date_format:H:i',
        ]);

        $data = $request->only(['name', 'address', 'latitude', 'longitude', 'phone', 'whatsapp_number', 'tagline', 'logo_size', 'auto_close_time']);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'rapid_laundromat/logos',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $data['logo_path'] = $result['secure_url'];
                } catch (\Exception $e) {
                    $filename = time().'_'.$file->getClientOriginalName();
                    $file->move(public_path('logos'), $filename);
                    $data['logo_path'] = 'logos/'.$filename;
                }
            } else {
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('logos'), $filename);
                $data['logo_path'] = 'logos/'.$filename;
            }
        }

        $branch->update($data);

        if (auth()->user()->role === 'super_admin') {
            if ($request->has('company_name')) {
                \App\Models\Setting::set('company_name', $request->company_name);
            }
            if ($request->has('auto_close_time')) {
                Branch::query()->update(['auto_close_time' => $request->auto_close_time]);
            }
        }

        return redirect()->back()->with('success', 'General settings saved.');
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item',
            'inventory' => 'nullable|array',
        ]);

        $service = ServiceType::create([
            'branch_id' => auth()->user()->branch_id ?? 1,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'base_price' => $request->base_price,
            'price_type' => $request->price_type,
            'icon' => $request->icon ?? '👕',
            'color' => $request->color ?? 'blue',
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, ServiceType $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item',
            'is_active' => 'boolean',
            'inventory' => 'nullable|array',
        ]);
        $service->update($request->only(['name', 'category_id', 'base_price', 'price_type', 'is_active', 'icon', 'color']));

        return redirect()->back()->with('success', 'Service updated.');
    }

    public function destroyService(ServiceType $service)
    {
        $service->delete();

        return redirect()->back()->with('success', 'Service deleted.');
    }

    public function storeServiceCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'rapid_laundromat/categories',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $imagePath = $result['secure_url'];
                } catch (\Exception $e) {
                    $filename = time().'_cat_'.$file->getClientOriginalName();
                    $file->move(public_path('categories'), $filename);
                    $imagePath = 'categories/'.$filename;
                }
            } else {
                $filename = time().'_cat_'.$file->getClientOriginalName();
                $file->move(public_path('categories'), $filename);
                $imagePath = 'categories/'.$filename;
            }
        }

        ServiceCategory::create([
            'branch_id' => auth()->user()->branch_id ?? 1,
            'name' => $request->name,
            'is_active' => true,
            'image_path' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Service Category created.');
    }

    public function updateServiceCategory(Request $request, ServiceCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $data = $request->only(['name', 'is_active']);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (env('CLOUDINARY_URL')) {
                try {
                    $cloudinary = new \Cloudinary\Cloudinary(env('CLOUDINARY_URL'));
                    $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'rapid_laundromat/categories',
                        'format' => 'webp',
                        'quality' => 'auto'
                    ]);
                    $data['image_path'] = $result['secure_url'];
                } catch (\Exception $e) {
                    $filename = time().'_cat_'.$file->getClientOriginalName();
                    $file->move(public_path('categories'), $filename);
                    $data['image_path'] = 'categories/'.$filename;
                }
            } else {
                $filename = time().'_cat_'.$file->getClientOriginalName();
                $file->move(public_path('categories'), $filename);
                $data['image_path'] = 'categories/'.$filename;
            }
        }

        $category->update($data);

        return redirect()->back()->with('success', 'Service Category updated.');
    }

    public function destroyServiceCategory(ServiceCategory $category)
    {
        $category->delete();

        return redirect()->back()->with('success', 'Service Category deleted.');
    }

    public function storeCubby(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $branchId = auth()->user()->branch_id ?? 1;
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
        $branchId = auth()->user()->branch_id ?? 1;
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
        $branchId = auth()->user()->branch_id ?? 1;
        try {
            $response = Http::timeout(5)->get("http://127.0.0.1:3005/api/whatsapp/status/{$branchId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'qr' => null, 'error' => 'Service offline']);
        }
    }

    public function whatsappLogout()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        try {
            $response = Http::post("http://127.0.0.1:3005/api/whatsapp/logout/{$branchId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }

    public function whatsappReset()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        try {
            $response = Http::post("http://127.0.0.1:3005/api/whatsapp/start/{$branchId}");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }

    public function storePackage(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
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
        $package->delete();

        return redirect()->back()->with('success', 'Package deleted successfully.');
    }

    public function updateDeliverySettings(Request $request)
    {
        abort_unless(auth()->user()->role === 'super_admin', 403);

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
        $request->validate([
            'min_distance_km' => 'required|numeric|min:0',
            'max_distance_km' => 'required|numeric|gt:min_distance_km',
            'cost' => 'required|numeric|min:0',
        ]);

        \App\Models\DeliveryRange::create([
            'branch_id' => auth()->user()->branch_id ?? 1,
            'min_distance_km' => $request->min_distance_km,
            'max_distance_km' => $request->max_distance_km,
            'cost' => $request->cost,
        ]);

        return redirect()->back()->with('success', 'Delivery Range added successfully.');
    }

    public function deleteDeliveryRange(\App\Models\DeliveryRange $deliveryRange)
    {
        $deliveryRange->delete();
        return redirect()->back()->with('success', 'Delivery Range deleted successfully.');
    }

    public function storePickupTimePeriod(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        \App\Models\PickupTimePeriod::create([
            'label' => $request->label,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Pickup time period added successfully.');
    }

    public function deletePickupTimePeriod(\App\Models\PickupTimePeriod $pickupTimePeriod)
    {
        $pickupTimePeriod->delete();
        return redirect()->back()->with('success', 'Pickup time period deleted successfully.');
    }
}
