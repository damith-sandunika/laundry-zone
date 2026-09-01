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
            [
                'type' => 'pos_walkin_completed',
                'name' => 'Walk-in Order Completed',
                'message' => 'Hi {customer_name}, your order #{order_id} is fully completed and settled. Thank you for your business! - RAPID Laundromat',
            ],
            [
                'type' => 'delivery_completed',
                'name' => 'Delivery Order Completed',
                'message' => 'Hi {customer_name}, your delivery order #{order_id} is completed. Thank you for using RAPID Laundromat!',
            ],
            [
                'type' => 'collection_order_placed',
                'name' => 'Collection Order Placed',
                'message' => 'Hi {customer_name}, your order #{order_id} has been placed at our collection branch and will be sent for processing.',
            ],
            [
                'type' => 'collection_order_ready',
                'name' => 'Collection Order Ready for Pickup/Delivery',
                'message' => 'Hi {customer_name}, great news! Your order #{order_id} is completed and ready for pickup or delivery.',
            ],
            [
                'type' => 'collection_order_completed',
                'name' => 'Collection Order Completed',
                'message' => 'Hi {customer_name}, your order #{order_id} is completed and settled. Thank you for choosing RAPID Laundromat!',
            ]
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
