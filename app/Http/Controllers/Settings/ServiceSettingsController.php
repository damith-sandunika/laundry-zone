<?php

namespace App\Http\Controllers\Settings;

use App\Actions\UploadImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreServiceCategoryRequest;
use App\Http\Requests\Settings\StoreServiceRequest;
use App\Http\Requests\Settings\UpdateServiceCategoryRequest;
use App\Http\Requests\Settings\UpdateServiceRequest;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Setting;
use Illuminate\Http\Request;

class ServiceSettingsController extends Controller
{
    protected function authorizeConfigManagement()
    {
        $isCentralized = Setting::get('centralized_management', '1') == '1';
        $isGlobalManager = auth()->user()->canManageGlobalConfig();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        if (!(($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin))) {
            abort(403, 'Unauthorized config management mode.');
        }
    }

    public function printPriceList()
    {
        $this->authorizeConfigManagement();
        $branchId = $this->getActiveBranchId();
        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();

        $serviceCategories = ServiceCategory::where(fn($q) => !$isGlobalMode ? $q->whereNull('branch_id')->orWhere('branch_id', $branchId) : null)
            ->where('is_visible_on_website', true)
            ->with([
                'children' => fn($q) => $q->where('is_visible_on_website', true)->orderBy('order_index')
                    ->with(['services' => fn($sq) => $sq->where('is_visible_on_website', true)->orderBy('order_index')]),
                'services' => fn($q) => $q->where('is_visible_on_website', true)->orderBy('order_index')
            ])->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');
        $mainBranch = Branch::first();
        return view('settings.pages.price_list_print', compact('mainCategories', 'mainBranch'));
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
        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();

        $serviceCategories = ServiceCategory::where(fn($q) => !$isGlobalMode ? $q->whereNull('branch_id')->orWhere('branch_id', $branchId) : null)
            ->with(['children.services' => fn($q) => $q->orderBy('order_index'), 'services' => fn($q) => $q->orderBy('order_index')])
            ->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');
        $services = ServiceType::where(fn($q) => !$isGlobalMode ? $q->whereNull('branch_id')->orWhere('branch_id', $branchId) : null)
            ->with(['inventoryItems', 'category'])->orderBy('order_index')->get();

        return view('settings.services', compact('serviceCategories', 'mainCategories', 'services'));
    }

    public function storeService(StoreServiceRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        ServiceType::create([
            'branch_id'             => $branchId,
            'name'                  => $request->name,
            'item_code'             => $request->item_code,
            'category_id'           => $request->category_id,
            'base_price'            => $request->base_price,
            'price_type'            => $request->price_type,
            'icon'                  => $request->icon ?? '🧺',
            'color'                 => $request->color ?? 'blue',
            'is_active'             => true,
            'is_visible_on_website' => $request->has('is_visible_on_website'),
        ]);

        return redirect()->back()->with('success', 'Service added.');
    }

    public function updateService(UpdateServiceRequest $request, ServiceType $service)
    {
        $this->authorizeConfigManagement();
        $data = $request->only(['name', 'item_code', 'category_id', 'base_price', 'price_type', 'is_active', 'icon', 'color']);
        $data['is_visible_on_website'] = $request->has('is_visible_on_website');
        $service->update($data);
        return redirect()->back()->with('success', 'Service updated.');
    }

    public function destroyService(ServiceType $service)
    {
        $this->authorizeConfigManagement();
        $service->delete();
        return redirect()->back()->with('success', 'Service deleted.');
    }

    public function storeServiceCategory(StoreServiceCategoryRequest $request, UploadImageAction $uploader)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();
        $imagePath = $request->hasFile('image') ? $uploader->execute($request->file('image'), 'categories', 'cat') : null;

        ServiceCategory::create([
            'branch_id'             => $branchId,
            'parent_id'             => $request->parent_id,
            'name'                  => $request->name,
            'is_active'             => true,
            'image_path'            => $imagePath,
            'is_visible_on_website' => $request->has('is_visible_on_website'),
        ]);

        return redirect()->back()->with('success', 'Service Category created.');
    }

    public function updateServiceCategory(UpdateServiceCategoryRequest $request, ServiceCategory $category, UploadImageAction $uploader)
    {
        $this->authorizeConfigManagement();
        $data = $request->only(['name', 'parent_id', 'is_active']);
        $data['is_visible_on_website'] = $request->has('is_visible_on_website');
        if ($request->hasFile('image')) {
            $data['image_path'] = $uploader->execute($request->file('image'), 'categories', 'cat');
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
        $request->validate(['ordered_ids' => 'required|array', 'category_id' => 'nullable']);
        $categoryId = $request->category_id === 'null' ? null : $request->category_id;
        foreach ($request->ordered_ids as $index => $id) {
            if ($id) ServiceType::where('id', $id)->update(['order_index' => $index, 'category_id' => $categoryId]);
        }
        return response()->json(['success' => true]);
    }
}
