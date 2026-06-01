<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OnlineBooking;

class OnlineBookingController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;
        
        $query = OnlineBooking::orderBy('created_at', 'desc');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        $bookings = $query->get();
        return view('online_bookings.index', compact('bookings'));
    }

    public function updateStatus(Request $request, OnlineBooking $booking)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $booking->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Booking status updated to ' . ucfirst($request->status));
    }
}
