<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
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
            ['type' => 'order_confirmation', 'name' => 'Order Confirmation', 'message' => 'Hello {customer_name}, your order #{order_id} has been confirmed. Total: {total}'],
            ['type' => 'order_ready', 'name' => 'Order Ready', 'message' => 'Your order #{order_id} is ready for pickup/delivery.'],
            ['type' => 'order_delivered', 'name' => 'Order Delivered', 'message' => 'Your order #{order_id} has been delivered. Thank you!'],
            ['type' => 'bill_finalize', 'name' => 'Bill Finalized', 'message' => 'Your bill for order #{order_id} is finalized. Please pay {amount_due}.'],
            ['type' => 'delivery_confirmation', 'name' => 'Delivery Confirmation', 'message' => 'Our rider is on the way with your order #{order_id}.'],
            ['type' => 'delivery_thank_you', 'name' => 'Delivery Thank You', 'message' => 'Thank you for choosing our delivery service for order #{order_id}.'],
            ['type' => 'order_dispatched_to_workshop', 'name' => 'Sent to Workshop', 'message' => 'Your order #{order_id} has been dispatched to our workshop.'],
            ['type' => 'order_processing', 'name' => 'Processing at Workshop', 'message' => 'We are now processing your order #{order_id} at our workshop.'],
            ['type' => 'order_returned_from_workshop', 'name' => 'Back from Workshop', 'message' => 'Your order #{order_id} has returned from the workshop and will be ready soon.'],
            ['type' => 'order_completed', 'name' => 'Order Completed', 'message' => 'Your order #{order_id} is fully completed. Thank you for choosing us!'],
            ['type' => 'order_cancelled', 'name' => 'Order Cancelled', 'message' => 'Your order #{order_id} has been cancelled. Contact us for details.'],
        ];

        foreach ($defaults as $default) {
            $default['is_active'] = true;
            SmsTemplate::updateOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('sms-templates.index')->with('success', 'Default templates seeded');
    }
}
