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
            [
                'type' => 'order_confirmation', 
                'name' => 'Order Confirmation', 
                'subject' => 'Your Order is Confirmed - #{order_id}', 
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #2563eb; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Order Confirmed!</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Thank you for your order. We have successfully received your laundry order <strong>#{order_id}</strong>.</p><div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 24px 0;"><p style="margin: 0; font-size: 18px; color: #111827; font-weight: bold;">Estimated Total: <span style="color: #2563eb;">{total}</span></p></div><p style="font-size: 14px; color: #6b7280; line-height: 1.5;">Our team will process your items with the utmost care. We will notify you once the bill is finalized and your order is ready.</p></div><div style="background-color: #f8fafc; padding: 16px; text-align: center; border-top: 1px solid #e5e7eb;"><p style="margin: 0; font-size: 12px; color: #94a3b8;">&copy; RAPID Laundromat. All rights reserved.</p></div></div>'
            ],
            [
                'type' => 'order_ready', 
                'name' => 'Order Ready', 
                'subject' => 'Your Order is Ready - #{order_id}', 
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #10b981; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Your Order is Ready!</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Great news! Your laundry order <strong>#{order_id}</strong> is now fresh, clean, and ready for you.</p><div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 24px 0;"><p style="margin: 0; font-size: 18px; color: #111827; font-weight: bold;">Amount Due: <span style="color: #10b981;">{amount_due}</span></p></div><p style="font-size: 14px; color: #6b7280; line-height: 1.5;">If you requested delivery, our rider will contact you soon. Otherwise, you can pick it up at our branch during working hours.</p></div></div>'
            ],
            [
                'type' => 'order_delivered', 
                'name' => 'Order Delivered', 
                'subject' => 'Order Delivered - #{order_id}', 
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #8b5cf6; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Order Delivered Successfully</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Your laundry order <strong>#{order_id}</strong> has been successfully delivered to your location.</p><p style="font-size: 16px; color: #374151; margin-top: 24px;">We hope you love your fresh laundry!</p></div><div style="background-color: #f8fafc; padding: 16px; text-align: center; border-top: 1px solid #e5e7eb;"><p style="margin: 0; font-size: 12px; color: #94a3b8;">Thank you for choosing RAPID Laundromat.</p></div></div>'
            ],
            [
                'type' => 'bill_finalize', 
                'name' => 'Bill Finalized', 
                'subject' => 'Bill for Order #{order_id}', 
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #f59e0b; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Bill Finalized</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Our workshop has inspected and processed your laundry. The final bill for order <strong>#{order_id}</strong> is now ready.</p><div style="background-color: #fffbeb; padding: 16px; border-radius: 8px; margin: 24px 0; border: 1px solid #fde68a;"><p style="margin: 0; font-size: 16px; color: #92400e;">Total Amount: <strong>{total}</strong></p><p style="margin: 8px 0 0 0; font-size: 18px; color: #b45309; font-weight: bold;">Amount Due: {amount_due}</p></div><p style="font-size: 14px; color: #6b7280; line-height: 1.5;">Please complete the payment so we can proceed with handing over or delivering your order.</p></div></div>'
            ],
            [
                'type' => 'delivery_confirmation', 
                'name' => 'Delivery Confirmation', 
                'subject' => 'Delivery on the way - #{order_id}', 
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #06b6d4; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Delivery is on the way!</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Our rider has picked up your order <strong>#{order_id}</strong> and is currently on the way to your location.</p><p style="font-size: 14px; color: #6b7280; margin-top: 24px;">Please be available at your provided address to receive the items.</p></div></div>'
            ],
            [
                'type' => 'delivery_thank_you', 
                'name' => 'Delivery Thank You', 
                'subject' => 'Thank You for Choosing Us', 
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #ec4899; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Thank You!</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Thank you for using our delivery service for order <strong>#{order_id}</strong>.</p><p style="font-size: 16px; color: #374151; margin-top: 24px;">We truly appreciate your business and hope to serve you again soon!</p></div></div>'
            ],
        ];

        foreach ($defaults as $default) {
            EmailTemplate::updateOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('email-templates.index')->with('success', 'Default templates seeded');
    }
}
