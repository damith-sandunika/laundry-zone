<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        
        $query = \App\Models\InventoryItem::with(['branch', 'transactions' => function($q) {
            $q->latest()->take(5);
        }]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $items = $query->get();

        return view('inventory.index', compact('items'));
    }

    public function destroy(\App\Models\InventoryItem $item)
    {
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Unauthorized action.');
        }
        $item->delete();
        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted.');
    }

    public function bulkStockIn(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
        ]);

        $updatedCount = 0;
        foreach ($request->items as $data) {
            if (empty($data['quantity'])) continue;

            $item = \App\Models\InventoryItem::find($data['id']);
            if ($item) {
                // Notice: In the previous stockIn method, it did increment('quantity'), but the column is 'current_stock'
                // Let's use 'current_stock' to be safe, because create logic uses 'current_stock'.
                $item->increment('current_stock', (float) $data['quantity']);

                \App\Models\InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type'              => 'stock_in',
                    'quantity'          => $data['quantity'],
                    'reference'         => 'Bulk Restock',
                    'notes'             => $request->notes ?? null,
                    'created_by'        => auth()->id(),
                ]);
                $updatedCount++;
            }
        }

        return redirect()->back()->with('success', "Successfully restocked {$updatedCount} items.");
    }

    public function bulkStockOut(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
            'notes' => 'nullable|string'
        ]);

        $updatedCount = 0;
        foreach ($request->items as $data) {
            if (empty($data['quantity'])) continue;

            $item = \App\Models\InventoryItem::find($data['id']);
            if ($item) {
                $item->decrement('current_stock', (float) $data['quantity']);

                \App\Models\InventoryTransaction::create([
                    'inventory_item_id' => $item->id,
                    'type'              => 'out',
                    'quantity'          => $data['quantity'],
                    'reference'         => 'Bulk Stock Out',
                    'notes'             => $request->notes ?? null,
                    'created_by'        => auth()->id(),
                ]);
                $updatedCount++;
            }
        }

        return redirect()->back()->with('success', "Successfully stocked out {$updatedCount} items.");
    }

    public function stockOut(Request $request, \App\Models\InventoryItem $item)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'note' => 'required|string|max:255',
        ]);

        if ($item->current_stock < $request->quantity) {
            return redirect()->back()->with('error', 'Not enough stock available.');
        }

        $item->decrement('current_stock', (float) $request->quantity);

        \App\Models\InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'type'              => 'out',
            'quantity'          => $request->quantity,
            'reference'         => 'Manual Deduct: ' . $request->note,
            'created_by'        => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Stock deducted successfully.');
    }
}
