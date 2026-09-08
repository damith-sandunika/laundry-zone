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
            ['type' => 'pos_walkin_placed', 'name' => 'Walk-in Order Placed', 'content' => 'Hi {customer_name}, your order #{order_id} has been placed successfully. We will let you know once it is completed.'],
            ['type' => 'pos_walkin_ready', 'name' => 'Walk-in Order Ready', 'content' => 'Hi {customer_name}, great news! Your order #{order_id} is washed and ready to be picked up. Thank you! - 365 Laundry'],
            ['type' => 'pos_walkin_completed', 'name' => 'Walk-in Order Completed', 'content' => 'Hi {customer_name}, your order #{order_id} is fully completed. Total: {total}. Payment: {payment_status}. Thank you! - 365 Laundry'],
            ['type' => 'collection_order_placed', 'name' => 'Collection Order Placed', 'content' => 'Hi {customer_name}, your order #{order_id} has been placed at our collection branch and will be sent for processing.'],
            ['type' => 'collection_order_ready', 'name' => 'Collection Order Ready for Pickup', 'content' => 'Hi {customer_name}, great news! Your order #{order_id} is completed and ready for pickup.'],
            ['type' => 'collection_order_completed', 'name' => 'Collection Order Completed', 'content' => 'Hi {customer_name}, your order #{order_id} is completed and settled. Thank you for choosing 365 Laundry!'],
            ['type' => 'delivery_order_confirmed', 'name' => 'Delivery Order Confirmed', 'content' => 'Hi {customer_name}, your delivery order #{order_id} has been approved and confirmed. We will process it shortly. Track your order here: {tracking_link}'],
            ['type' => 'delivery_order_cancelled', 'name' => 'Delivery Order Cancelled', 'content' => 'Hi {customer_name}, unfortunately, your delivery order #{order_id} has been cancelled. Please contact us for details.'],
            ['type' => 'delivery_order_ready', 'name' => 'Delivery Order Ready for Dispatch', 'content' => 'Hi {customer_name}, your delivery order #{order_id} is washed and ready to be dispatched to you! Track your order here: {tracking_link}'],
            ['type' => 'delivery_completed', 'name' => 'Delivery Order Completed', 'content' => 'Hi {customer_name}, your delivery order #{order_id} has been successfully delivered. Thank you for using 365 Laundry! Track your order here: {tracking_link}'],
            ['type' => 'bill_finalized', 'name' => 'Payment Request', 'content' => 'Hi {customer_name}, the bill for order #{order_id} is finalized. Total: {total}. Requested Amount: {requested_amount}. Please arrange the payment.'],
            ['type' => 'payment_received', 'name' => 'Payment Received', 'content' => 'Hi {customer_name}, we have received and approved a payment for your order #{order_id}. Total: {total}. Status: {payment_status}.'],
        ];

        $defaultTypes = array_column($defaults, 'type');
        WhatsappTemplate::whereNotIn('type', $defaultTypes)->delete();

        foreach ($defaults as $default) {
            $default['is_active'] = true;
            $default['language_code'] = 'en';
            WhatsappTemplate::updateOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('whatsapp-templates.index')->with('success', 'Default templates seeded');
    }
}

