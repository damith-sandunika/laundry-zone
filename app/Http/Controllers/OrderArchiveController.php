<?php

namespace App\Http\Controllers;

use App\Models\OrderArchive;
use Illuminate\Http\Request;

class OrderArchiveController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('is-super-admin');

        $query = OrderArchive::orderBy('archived_at', 'desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('order_number', 'like', "%{$q}%");
        }

        $archives = $query->paginate(20);

        return view('orders.archive', compact('archives'));
    }
}
