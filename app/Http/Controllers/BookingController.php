<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OnlineBooking;
use App\Models\ServiceType;
use App\Models\WhatsappLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function page(Request $request)
    {
        $branchId = $request->query('branch', 1);
        $branch = Branch::find($branchId);
        $services = ServiceType::where('is_active', true)->get();

        return view('booking.page', compact('branch', 'services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string|max:255',
            'service_description' => 'required|string|max:500',
            'preferred_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $booking = DB::transaction(function () use ($validated) {
            $booking = OnlineBooking::create(array_merge($validated, ['status' => 'pending']));

            // Queue confirmation WhatsApp if phone provided
            $branch = Branch::find($validated['branch_id']);
            $msg = '*'.($branch->name ?? 'Smart Laundry')." - Booking Confirmed!* ✅\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= '📋 Booking ID: #'.str_pad($booking->id, 4, '0', STR_PAD_LEFT)."\n";
            $msg .= '👤 Name: '.$validated['customer_name']."\n";
            $msg .= '🧺 Service: '.$validated['service_description']."\n";
            $msg .= '📅 Preferred Date: '.date('d M Y', strtotime($validated['preferred_date']))."\n";
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
            $msg .= "We will contact you to confirm your booking.\n";
            $msg .= '_Thank you for choosing '.($branch->name ?? 'Smart Laundry').'!_';

            WhatsappLog::create([
                'order_id' => null,
                'customer_id' => null,
                'phone' => $validated['customer_phone'],
                'message_type' => 'booking_confirmation',
                'message' => $msg,
                'status' => 'pending',
            ]);

            return $booking;
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'booking_id' => $booking->id]);
        }

        return redirect()->route('booking.success', ['id' => $booking->id]);
    }

    public function success(Request $request)
    {
        $booking = OnlineBooking::findOrFail($request->query('id'));
        $branch = Branch::find($booking->branch_id);

        return view('booking.success', compact('booking', 'branch'));
    }
}
