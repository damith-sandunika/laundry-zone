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
            'is_active' => $request->boolean('is_active')
        ]);

        return redirect()->route('email-templates.index')->with('success', 'Email Template updated successfully');
    }

    public function seedDefault()
    {
        $defaults = [
            [
                'type' => 'pos_walkin_placed',
                'name' => 'Walk-in Order Placed',
                'subject' => 'Walk-in Order Placed',
                'body' => '<p>Hi {customer_name}, your order #{order_id} has been placed successfully. We will let you know once it is completed.</p>',
            ],
            [
                'type' => 'pos_walkin_ready',
                'name' => 'Walk-in Order Ready',
                'subject' => 'Walk-in Order Ready',
                'body' => '<p>Hi {customer_name}, your order #{order_id} is washed and ready to be picked up. Thank you! - 365 Laundry</p>',
            ],
            [
                'type' => 'pos_walkin_completed',
                'name' => 'Walk-in Order Completed',
                'subject' => 'Walk-in Order Completed',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #10b981; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Order Completed</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Your order <strong>#{order_id}</strong> is fully completed and settled.</p><p style="font-size: 14px; color: #6b7280; line-height: 1.5;">Thank you for your business! - 365 Laundry</p></div></div>'
            ],
            [
                'type' => 'delivery_completed',
                'name' => 'Delivery Order Completed',
                'subject' => 'Delivery Order Completed',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #3b82f6; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Delivery Completed</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Your delivery order <strong>#{order_id}</strong> is completed. Thank you for using 365 Laundry!</p></div></div>'
            ],
            [
                'type' => 'collection_order_placed',
                'name' => 'Collection Order Placed',
                'subject' => 'Collection Order Placed',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #f59e0b; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Order Placed</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Your order <strong>#{order_id}</strong> has been placed at our collection branch and will be sent for processing.</p></div></div>'
            ],
            [
                'type' => 'collection_order_ready',
                'name' => 'Collection Order Ready for Pickup/Delivery',
                'subject' => 'Collection Order Ready',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #10b981; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Order Ready</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Great news! Your order <strong>#{order_id}</strong> is completed and ready for pickup or delivery.</p></div></div>'
            ],
            [
                'type' => 'collection_order_completed',
                'name' => 'Collection Order Completed',
                'subject' => 'Collection Order Completed',
                'body' => '<div style="font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;"><div style="background-color: #8b5cf6; padding: 24px; text-align: center;"><h1 style="color: #ffffff; margin: 0; font-size: 24px;">Order Completed</h1></div><div style="padding: 32px; background-color: #ffffff;"><p style="font-size: 16px; color: #374151; margin-bottom: 24px;">Hi {customer_name},</p><p style="font-size: 16px; color: #374151; line-height: 1.5;">Your order <strong>#{order_id}</strong> is completed and settled. Thank you for choosing 365 Laundry!</p></div></div>'
            ]
        ];

        $defaultTypes = array_column($defaults, 'type');
        EmailTemplate::whereNotIn('type', $defaultTypes)->delete();

        foreach ($defaults as $default) {
            $default['is_active'] = true;
            EmailTemplate::updateOrCreate(['type' => $default['type']], $default);
        }

        return redirect()->route('email-templates.index')->with('success', 'Default templates seeded');
    }
}


