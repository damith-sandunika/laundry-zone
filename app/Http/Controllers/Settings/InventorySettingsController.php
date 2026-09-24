<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreInventoryRequest;
use App\Http\Requests\Settings\StockInRequest;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;

class InventorySettingsController extends Controller
{
    public function storeInventory(StoreInventoryRequest $request)
    {
        $branchId = $this->getActiveBranchId();

        InventoryItem::create([
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

    public function stockIn(StockInRequest $request, InventoryItem $item)
    {
        $item->increment('quantity', $request->quantity);

        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'type'              => 'stock_in',
            'quantity'          => $request->quantity,
            'reference'         => $request->reference ?? 'Manual stock-in',
            'notes'             => $request->notes ?? null,
            'created_by'        => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Stock updated.');
    }
}
