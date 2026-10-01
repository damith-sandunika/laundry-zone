<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreServiceRequest;
use App\Http\Requests\Settings\UpdateServiceRequest;
use App\Http\Requests\Settings\StoreServiceCategoryRequest;
use App\Http\Requests\Settings\UpdateServiceCategoryRequest;
use App\Http\Requests\Settings\StoreExtraRequest;
use App\Http\Requests\Settings\UpdateExtraRequest;
use App\Http\Requests\Settings\StoreTurnaroundTypeRequest;
use App\Http\Requests\Settings\UpdateTurnaroundTypeRequest;
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
        $canManage = ($isCentralized && $isGlobalManager) || (!$isCentralized && !$isSuperAdmin);
        if (!$canManage) abort(403, 'Unauthorized config management mode.');
    }

    public function printPriceList()
    {
        $this->authorizeConfigManagement();
        $branchId = $this->getActiveBranchId();
        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();

        // Get categories with their services that are visible on website
        $serviceCategories = ServiceCategory::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->where('is_visible_on_website', true)
          ->with(['children' => function($q) use ($branchId, $isGlobalMode) {
               $q->where('is_visible_on_website', true);
               if (!$isGlobalMode) $q->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $branchId));
               $q->orderBy('order_index');
               $q->with(['services' => function($sq) use ($branchId, $isGlobalMode) {
                   $sq->where('is_visible_on_website', true);
                   if (!$isGlobalMode) $sq->where(fn($ssq) => $ssq->whereNull('branch_id')->orWhere('branch_id', $branchId));
                   $sq->orderBy('order_index');
               }]);
          }, 'services' => function ($q) use ($branchId, $isGlobalMode) {
               $q->where('is_visible_on_website', true);
               if (!$isGlobalMode) $q->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $branchId));
               $q->orderBy('order_index');
          }])->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');
        $mainBranch = \App\Models\Branch::first();

        return view('settings.pages.price_list_print', compact('mainCategories', 'mainBranch'));
    }

    public function servicesIndex()
    {
        $services = ServiceType::all();
        $inventoryItems = \App\Models\InventoryItem::where('branch_id', $this->getActiveBranchId())->get();
        return view('services.index', compact('services', 'inventoryItems'));
    }

    public function servicesManager()
    {
        $this->authorizeConfigManagement();
        $branchId = $this->getActiveBranchId();
        $isGlobalMode = Setting::get('centralized_management', '1') == '1' && auth()->user()->canManageGlobalConfig();

        $serviceCategories = ServiceCategory::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->with(['children.services' => function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $branchId));
            $q->orderBy('order_index');
        }, 'services' => function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->where(fn($sq) => $sq->whereNull('branch_id')->orWhere('branch_id', $branchId));
            $q->orderBy('order_index');
        }])->orderBy('order_index')->get();

        $mainCategories = $serviceCategories->whereNull('parent_id');
        $services = ServiceType::where(function ($q) use ($branchId, $isGlobalMode) {
            if (!$isGlobalMode) $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
        })->with(['inventoryItems', 'category'])->orderBy('order_index')->get();

        return view('settings.services', compact('serviceCategories', 'mainCategories', 'services'));
    }

    public function storeService(StoreServiceRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        ServiceType::create([
            'branch_id'               => $branchId,
            'name'                    => $request->name,
            'item_code'               => $request->item_code,
            'category_id'             => $request->category_id,
            'base_price'              => $request->base_price,
            'price_type'              => $request->price_type,
            'icon'                    => $request->icon ?? '🧺',
            'color'                   => $request->color ?? 'blue',
            'is_active'               => true,
            'is_visible_on_website'   => $request->has('is_visible_on_website'),
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

    public function storeServiceCategory(StoreServiceCategoryRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = app(\App\Actions\UploadImageAction::class)->execute($request->file('image'), 'categories', 'cat');
        }

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

    public function updateServiceCategory(UpdateServiceCategoryRequest $request, ServiceCategory $category)
    {
        $this->authorizeConfigManagement();
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
        $request->validate(['ordered_ids' => 'required|array', 'category_id' => 'nullable']);
        $categoryId = $request->category_id === 'null' ? null : $request->category_id;
        foreach ($request->ordered_ids as $index => $id) {
            if ($id) {
                ServiceType::where('id', $id)->update(['order_index' => $index, 'category_id' => $categoryId]);
            }
        }
        return response()->json(['success' => true]);
    }

    // ─── Extras ───

    public function storeExtra(StoreExtraRequest $request)
    {
        $this->authorizeConfigManagement();
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        \App\Models\Extra::create([
            'branch_id'  => $branchId,
            'name'       => $request->name,
            'price_type' => $request->price_type,
            'price'      => $request->price,
            'is_active'  => true,
        ]);
        return redirect()->back()->with('success', 'Extra added successfully.');
    }

    public function updateExtra(UpdateExtraRequest $request, \App\Models\Extra $extra)
    {
        $this->authorizeConfigManagement();
        $extra->update($request->only(['name', 'price_type', 'price', 'is_active']));
        return redirect()->back()->with('success', 'Extra updated successfully.');
    }

    public function destroyExtra(\App\Models\Extra $extra)
    {
        $this->authorizeConfigManagement();
        $extra->delete();
        return redirect()->back()->with('success', 'Extra deleted successfully.');
    }

    // ─── Turnaround Types ───

    public function storeTurnaroundType(StoreTurnaroundTypeRequest $request)
    {
        $this->authorizeConfigManagement();
        if ($request->has('is_default')) {
            \App\Models\TurnaroundType::query()->update(['is_default' => false]);
        }
        $branchId = Setting::get('centralized_management', '1') == '1' ? null : $this->getActiveBranchId();

        \App\Models\TurnaroundType::create([
            'branch_id'    => $branchId,
            'name'         => $request->name,
            'markup_type'  => $request->markup_type,
            'markup_value' => $request->markup_value,
            'is_default'   => $request->has('is_default'),
            'is_active'    => true,
        ]);
        return redirect()->back()->with('success', 'Turnaround type added successfully.');
    }

    public function updateTurnaroundType(UpdateTurnaroundTypeRequest $request, \App\Models\TurnaroundType $turnaroundType)
    {
        $this->authorizeConfigManagement();
        if ($request->has('is_default') && $request->is_default) {
            \App\Models\TurnaroundType::where('id', '!=', $turnaroundType->id)->update(['is_default' => false]);
        }
        $turnaroundType->update([
            'name'         => $request->name,
            'markup_type'  => $request->markup_type,
            'markup_value' => $request->markup_value,
            'is_default'   => $request->has('is_default'),
            'is_active'    => $request->has('is_active'),
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
