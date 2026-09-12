<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        // Auto-seed rider_assigned if missing
        if (!SmsTemplate::where('type', 'rider_assigned')->exists()) {
            SmsTemplate::create([
                'type' => 'rider_assigned',
                'name' => 'Delivery Rider Assigned',
                'message' => 'Hi {customer_name}, your order #{order_id} has been assigned to our rider {rider_name} (Phone: {rider_phone}, Vehicle: {rider_vehicle_number}). - 365laundry.lk',
                'is_active' => true,
            ]);
        }

        $templates = SmsTemplate::all();
        $isSmsEnabled = Setting::where('key', 'is_sms_enabled')->value('value') === 'true';

        return view('superadmin.sms-templates.index', compact('templates', 'isSmsEnabled'));
    }

    public function toggleGlobal(Request $request)
    {
        $isEnabled = $request->input('is_sms_enabled');
        Setting::updateOrCreate(
            ['key' => 'is_sms_enabled'],
            ['value' => $isEnabled ? 'true' : 'false']
        );

        return back()->with('success', 'Global SMS setting updated');
    }

    public function update(Request $request, SmsTemplate $template)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $template->update([
            'message'   => $request->message,
            'is_active' => $request->input('is_active') == '1',
        ]);

        return redirect()->route('sms-templates.index')->with('success', 'SMS Template updated successfully');
    }

    public function seedDefault()
    {
        $defaults = [
            [
                'type' => 'pos_walkin_placed',
                'name' => 'Walk-in Order Placed',
                'message' => 'Hi {customer_name}, your order #{order_id} has been placed successfully. We will let you know once it is completed.',
            ],
            [
                'type' => 'pos_walkin_ready',
                'name' => 'Walk-in Order Ready',
                'message' => 'Hi {customer_name}, great news! Your order #{order_id} is washed and ready to be picked up. Thank you! - 365 Laundry',
            ],
            [
                'type' => 'pos_walkin_completed',
                'name' => 'Walk-in Order Completed',
                'message' => 'Hi {customer_name}, your order #{order_id} is fully completed. Total: {total}. Payment: {payment_status}. Thank you! - 365 Laundry',
            ],
            [
                'type' => 'collection_order_placed',
                'name' => 'Collection Order Placed',
                'message' => 'Hi {customer_name}, your order #{order_id} has been placed at our collection branch and will be sent for processing.',
            ],
            [
                'type' => 'collection_order_ready',
                'name' => 'Collection Order Ready for Pickup',
                'message' => 'Hi {customer_name}, great news! Your order #{order_id} is completed and ready for pickup.',
            ],
            [
                'type' => 'collection_order_completed',
                'name' => 'Collection Order Completed',
                'message' => 'Hi {customer_name}, your order #{order_id} is completed and settled. Thank you for choosing 365 Laundry!',
            ],
            [
                'type' => 'delivery_order_confirmed',
                'name' => 'Delivery Order Confirmed',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} has been approved and confirmed. We will process it shortly. Track your order here: {tracking_link}',
            ],
            [
                'type' => 'delivery_order_cancelled',
                'name' => 'Delivery Order Cancelled',
                'message' => 'Hi {customer_name}, unfortunately, your delivery order #{order_id} has been cancelled. Please contact us for details. Track your order here: {tracking_link}',
            ],
            [
                'type' => 'delivery_order_ready',
                'name' => 'Delivery Order Ready for Dispatch',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} is washed and ready to be dispatched to you! Track your order here: {tracking_link}',
            ],
            [
                'type' => 'delivery_completed',
                'name' => 'Delivery Order Completed',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} has been successfully delivered. Thank you for using 365 Laundry! Track your order here: {tracking_link}',
            ],
            [
                'type' => 'bill_finalized',
                'name' => 'Payment Request',
                'message' => 'Hi {customer_name}, the bill for order #{order_id} is finalized. Total: {total}. Requested Amount: {requested_amount}. Please arrange the payment.',
            ],
            [
                'type' => 'payment_received',
                'name' => 'Payment Received',
                'message' => 'Hi {customer_name}, we have received and approved a payment for your order #{order_id}. Total: {total}. Status: {payment_status}.',
            ],
            [
                'type' => 'rider_assigned',
                'name' => 'Delivery Rider Assigned',
                'message' => 'Hi {customer_name}, your order #{order_id} has been assigned to our rider {rider_name} (Phone: {rider_phone}, Vehicle: {rider_vehicle_number}). - 365laundry.lk',
            ],
        ];

        $defaultTypes = array_column($defaults, 'type');
        SmsTemplate::whereNotIn('type', $defaultTypes)->delete();

        foreach ($defaults as $default) {
            $default['is_active'] = true;
            SmsTemplate::updateOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('sms-templates.index')->with('success', 'Default templates seeded');
    }
}

