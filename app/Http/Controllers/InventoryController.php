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
}
