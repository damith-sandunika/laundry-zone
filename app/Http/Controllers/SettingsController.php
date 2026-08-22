<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\WhatsAppService;

class SettingsController extends Controller
{
    public function servicesIndex()
    {
        $services = \App\Models\ServiceType::all();
        $inventoryItems = \App\Models\InventoryItem::where('branch_id', auth()->user()->branch_id ?? 1)->get();
        return view('services.index', compact('services', 'inventoryItems'));
    }

    public function index()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $branch = \App\Models\Branch::find($branchId);
        $serviceCategories = \App\Models\ServiceCategory::where('branch_id', $branchId)->with('services')->get();
        $services = \App\Models\ServiceType::with(['inventoryItems', 'category'])->get();
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->get();
        $inventoryItems = \App\Models\InventoryItem::where('branch_id', $branchId)->get();
        $packages = \App\Models\Package::where('branch_id', $branchId)->with('services.serviceType')->get();
        return view('settings.index', compact('branch', 'serviceCategories', 'services', 'cubbies', 'inventoryItems', 'packages'));
    }

    public function updateGeneral(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $branch = \App\Models\Branch::find($branchId);

        $request->validate([
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string|max:255',
            'phone'            => 'nullable|string|max:50',
            'whatsapp_number'  => 'nullable|string|max:50',
            'tagline'          => 'nullable|string|max:255',
            'logo_size'        => 'nullable|integer|min:20|max:300',
            'logo'             => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'address', 'phone', 'whatsapp_number', 'tagline', 'logo_size']);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('logos'), $filename);
            $data['logo_path'] = 'logos/' . $filename;
        }

        $branch->update($data);
        return redirect()->back()->with('success', 'General settings saved.');
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price'  => 'required|numeric|min:0',
            'price_type'  => 'required|in:per_kg,per_item',
            'inventory'   => 'nullable|array',
        ]);

        $service = \App\Models\ServiceType::create([
            'name'        => $request->name,
            'category_id' => $request->category_id,
            'base_price'  => $request->base_price,
            'price_type'  => $request->price_type,
            'icon'        => $request->icon ?? '👕',
            'color'       => $request->color ?? 'blue',
            'is_active'   => true,
        ]);

        return redirect()->back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, \App\Models\ServiceType $service)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:service_categories,id',
            'base_price'  => 'required|numeric|min:0',
            'price_type'  => 'required|in:per_kg,per_item',
            'is_active'   => 'boolean',
            'inventory'   => 'nullable|array',
        ]);
        $service->update($request->only(['name', 'category_id', 'base_price', 'price_type', 'is_active', 'icon', 'color']));

        return redirect()->back()->with('success', 'Service updated.');
    }

    public function destroyService(\App\Models\ServiceType $service)
    {
        $service->delete();
        return redirect()->back()->with('success', 'Service deleted.');
    }

    public function storeServiceCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        \App\Models\ServiceCategory::create([
            'branch_id' => auth()->user()->branch_id ?? 1,
            'name'      => $request->name,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Service Category created.');
    }

    public function updateServiceCategory(Request $request, \App\Models\ServiceCategory $category)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $category->update($request->only(['name', 'is_active']));
        return redirect()->back()->with('success', 'Service Category updated.');
    }

    public function destroyServiceCategory(\App\Models\ServiceCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Service Category deleted.');
    }

    public function storeCubby(Request $request)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $branchId = auth()->user()->branch_id ?? 1;
        \App\Models\Cubby::create([
            'branch_id' => $branchId,
            'name'      => $request->name,
            'status'    => 'empty',
        ]);
        return redirect()->back()->with('success', 'Storage location added.');
    }

    public function updateCubby(Request $request, \App\Models\Cubby $cubby)
    {
        $request->validate(['name' => 'required|string|max:50']);
        $cubby->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Storage location updated.');
    }

    public function destroyCubby(\App\Models\Cubby $cubby)
    {
        $cubby->delete();
        return redirect()->back()->with('success', 'Storage location deleted.');
    }

    public function storeInventory(Request $request)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $request->validate([
            'name'                => 'required|string|max:255',
            'unit'                => 'required|string|max:50',
            'current_stock'       => 'required|numeric|min:0',
            'min_stock'           => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'location'            => 'nullable|string|max:255',
        ]);
        \App\Models\InventoryItem::create([
            'branch_id'           => $branchId,
            'name'                => $request->name,
            'unit'                => $request->unit,
            'current_stock'       => $request->current_stock,
            'min_stock'           => $request->min_stock ?? 0,
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            'location'            => $request->location,
        ]);
        return redirect()->back()->with('success', 'Inventory item added.');
    }

    public function stockIn(Request $request, \App\Models\InventoryItem $item)
    {
        $request->validate(['quantity' => 'required|numeric|min:0.01']);
        $item->increment('quantity', $request->quantity);
        \App\Models\InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'type'              => 'stock_in',
            'quantity'          => $request->quantity,
            'reference'         => $request->reference ?? 'Manual stock-in',
            'notes'             => $request->notes ?? null,
            'created_by'        => auth()->id(),
        ]);
        return redirect()->back()->with('success', 'Stock updated.');
    }

    public function whatsappStatus()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("http://127.0.0.1:3005/api/whatsapp/status/{$branchId}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'qr' => null, 'error' => 'Service offline']);
        }
    }

    public function whatsappLogout()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        try {
            $response = \Illuminate\Support\Facades\Http::post("http://127.0.0.1:3005/api/whatsapp/logout/{$branchId}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Service offline']);
        }
    }

    public function whatsappReset()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        try {
            $response = \Illuminate\Support\Facades\Http::post("http://127.0.0.1:3005/api/whatsapp/start/{$branchId}");
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
            'services.*' => 'nullable|numeric|min:0'
        ]);

        $package = \App\Models\Package::create([
            'branch_id' => $branchId,
            'name' => $request->name,
            'price' => $request->price,
            'payment_type' => $request->payment_type,
            'is_active' => true,
        ]);

        foreach ($request->services as $serviceId => $limit) {
            if ($limit > 0) {
                \App\Models\PackageService::create([
                    'package_id' => $package->id,
                    'service_type_id' => $serviceId,
                    'quantity_limit' => $limit
                ]);
            }
        }

        return redirect()->back()->with('success', 'Package created successfully.');
    }

    public function updatePackage(Request $request, \App\Models\Package $package)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'payment_type' => 'required|in:prepaid,postpaid',
            'services' => 'required|array',
            'services.*' => 'nullable|numeric|min:0'
        ]);

        $package->update([
            'name' => $request->name,
            'price' => $request->price,
            'payment_type' => $request->payment_type,
        ]);

        // Re-create services
        \App\Models\PackageService::where('package_id', $package->id)->delete();
        foreach ($request->services as $serviceId => $limit) {
            if ($limit > 0) {
                \App\Models\PackageService::create([
                    'package_id' => $package->id,
                    'service_type_id' => $serviceId,
                    'quantity_limit' => $limit
                ]);
            }
        }

        return redirect()->back()->with('success', 'Package updated successfully.');
    }

    public function destroyPackage(\App\Models\Package $package)
    {
        $package->delete();
        return redirect()->back()->with('success', 'Package deleted successfully.');
    }
}
