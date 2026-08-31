<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WhatsappTemplate;
use Illuminate\Http\Request;

class WhatsappTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::all();
        $isWhatsappEnabled = Setting::where('key', 'is_whatsapp_enabled')->value('value') === 'true';
        $whatsappPhoneId = Setting::where('key', 'whatsapp_phone_id')->value('value');
        $whatsappAccessToken = Setting::where('key', 'whatsapp_access_token')->value('value');

        return view('superadmin.whatsapp-templates.index', compact('templates', 'isWhatsappEnabled', 'whatsappPhoneId', 'whatsappAccessToken'));
    }

    public function toggleGlobal(Request $request)
    {
        $isEnabled = $request->input('is_whatsapp_enabled');
        Setting::updateOrCreate(
            ['key' => 'is_whatsapp_enabled'],
            ['value' => $isEnabled ? 'true' : 'false']
        );

        Setting::updateOrCreate(
            ['key' => 'whatsapp_phone_id'],
            ['value' => $request->input('whatsapp_phone_id')]
        );

        Setting::updateOrCreate(
            ['key' => 'whatsapp_access_token'],
            ['value' => $request->input('whatsapp_access_token')]
        );

        return back()->with('success', 'WhatsApp configuration updated successfully');
    }

    public function update(Request $request, WhatsappTemplate $template)
    {
        $request->validate([
            'meta_template_name' => 'nullable|string',
            'language_code' => 'required|string',
            'content' => 'nullable|string',
        ]);

        $template->update([
            'meta_template_name' => $request->meta_template_name,
            'language_code'      => $request->language_code,
            'content'            => $request->content,
            'is_active'          => $request->input('is_active') == '1',
        ]);

        return redirect()->route('whatsapp-templates.index')->with('success', 'WhatsApp Template updated successfully');
    }

    public function seedDefault()
    {
        $defaults = [
            ['type' => 'order_confirmation', 'name' => 'Order Confirmation', 'content' => 'Hello {customer_name}, your order #{order_id} has been confirmed. Total: {total}'],
            ['type' => 'order_ready', 'name' => 'Order Ready', 'content' => 'Your order #{order_id} is ready for pickup/delivery.'],
            ['type' => 'order_delivered', 'name' => 'Order Delivered', 'content' => 'Your order #{order_id} has been delivered. Thank you!'],
            ['type' => 'bill_finalize', 'name' => 'Bill Finalized', 'content' => 'Your bill for order #{order_id} is finalized. Please pay {amount_due}.'],
            ['type' => 'delivery_confirmation', 'name' => 'Delivery Confirmation', 'content' => 'Our rider is on the way with your order #{order_id}.'],
            ['type' => 'delivery_thank_you', 'name' => 'Delivery Thank You', 'content' => 'Thank you for choosing our delivery service for order #{order_id}.'],
            ['type' => 'order_dispatched_to_workshop', 'name' => 'Sent to Workshop', 'content' => 'Your order #{order_id} has been dispatched to our workshop.'],
            ['type' => 'order_processing', 'name' => 'Processing at Workshop', 'content' => 'We are now processing your order #{order_id} at our workshop.'],
            ['type' => 'order_returned_from_workshop', 'name' => 'Back from Workshop', 'content' => 'Your order #{order_id} has returned from the workshop and will be ready soon.'],
            ['type' => 'order_completed', 'name' => 'Order Completed', 'content' => 'Your order #{order_id} is fully completed. Thank you for choosing us!'],
            ['type' => 'order_cancelled', 'name' => 'Order Cancelled', 'content' => 'Your order #{order_id} has been cancelled. Contact us for details.'],
        ];

        foreach ($defaults as $default) {
            $default['is_active'] = true;
            $default['language_code'] = 'en';
            WhatsappTemplate::updateOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('whatsapp-templates.index')->with('success', 'Default templates seeded');
    }
}
