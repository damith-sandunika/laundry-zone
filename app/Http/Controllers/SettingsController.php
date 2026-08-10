<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        $services = \App\Models\ServiceType::with('inventoryItems')->get();
        $cubbies = \App\Models\Cubby::where('branch_id', $branchId)->get();
        $inventoryItems = \App\Models\InventoryItem::where('branch_id', $branchId)->get();
        return view('settings.index', compact('branch', 'services', 'cubbies', 'inventoryItems'));
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
            'name'       => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item',
            'inventory'  => 'nullable|array',
        ]);

        $service = \App\Models\ServiceType::create([
            'name'       => $request->name,
            'base_price' => $request->base_price,
            'price_type' => $request->price_type,
            'icon'       => $request->icon ?? '👕',
            'color'      => $request->color ?? 'blue',
            'is_active'  => true,
        ]);

        if ($request->inventory) {
            foreach ($request->inventory as $itemId => $qty) {
                if ($qty > 0) {
                    $service->inventoryItems()->attach($itemId, [
                        'qty_per_use'    => $qty,
                        'deduct_at_stage'=> 'washing',
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, \App\Models\ServiceType $service)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_type' => 'required|in:per_kg,per_item',
            'is_active'  => 'boolean',
            'inventory'  => 'nullable|array',
        ]);
        $service->update($request->only(['name', 'base_price', 'price_type', 'is_active', 'icon', 'color']));

        // Sync inventory links
        if ($request->has('inventory')) {
            $syncData = [];
            foreach ($request->inventory as $itemId => $qty) {
                if ($qty > 0) {
                    $syncData[$itemId] = ['qty_per_use' => $qty, 'deduct_at_stage' => 'washing'];
                }
            }
            $service->inventoryItems()->sync($syncData);
        }

        return redirect()->back()->with('success', 'Service updated.');
    }

    public function destroyService(\App\Models\ServiceType $service)
    {
        $service->delete();
        return redirect()->back()->with('success', 'Service deleted.');
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
        try {
            $response = Http::timeout(3)->get('http://127.0.0.1:3001/api/whatsapp/status');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['connected' => false, 'error' => true], 500);
        }
    }

    public function whatsappLogout()
    {
        try {
            $response = Http::timeout(3)->post('http://127.0.0.1:3001/api/whatsapp/logout');
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => true], 500);
        }
    }
}
