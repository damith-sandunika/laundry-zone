<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::all();
        $isEmailEnabled = Setting::where('key', 'is_email_enabled')->value('value') === 'true';

        return view('superadmin.email-templates.index', compact('templates', 'isEmailEnabled'));
    }

    public function toggleGlobal(Request $request)
    {
        $isEnabled = $request->input('is_email_enabled');
        Setting::updateOrCreate(
            ['key' => 'is_email_enabled'],
            ['value' => $isEnabled ? 'true' : 'false']
        );

        return back()->with('success', 'Global Email setting updated');
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('email-templates.index')->with('success', 'Email Template updated successfully');
    }

    public function seedDefault()
    {
        $defaults = [
            ['type' => 'order_confirmation', 'name' => 'Order Confirmation', 'subject' => 'Your Order is Confirmed - #{order_id}', 'body' => '<h1>Hello {customer_name},</h1><p>Your order #{order_id} has been confirmed. Total: {total}</p>'],
            ['type' => 'order_ready', 'name' => 'Order Ready', 'subject' => 'Your Order is Ready - #{order_id}', 'body' => '<h1>Hello,</h1><p>Your order #{order_id} is ready for pickup/delivery.</p>'],
            ['type' => 'order_delivered', 'name' => 'Order Delivered', 'subject' => 'Order Delivered - #{order_id}', 'body' => '<h1>Hello,</h1><p>Your order #{order_id} has been delivered. Thank you!</p>'],
            ['type' => 'bill_finalize', 'name' => 'Bill Finalized', 'subject' => 'Bill for Order #{order_id}', 'body' => '<h1>Hello,</h1><p>Your bill for order #{order_id} is finalized. Please pay {amount_due}.</p>'],
            ['type' => 'delivery_confirmation', 'name' => 'Delivery Confirmation', 'subject' => 'Delivery on the way - #{order_id}', 'body' => '<h1>Hello,</h1><p>Our rider is on the way with your order #{order_id}.</p>'],
            ['type' => 'delivery_thank_you', 'name' => 'Delivery Thank You', 'subject' => 'Thank You for Choosing Us', 'body' => '<h1>Hello,</h1><p>Thank you for choosing our delivery service for order #{order_id}.</p>'],
        ];

        foreach ($defaults as $default) {
            EmailTemplate::firstOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('email-templates.index')->with('success', 'Default templates seeded');
    }
}
