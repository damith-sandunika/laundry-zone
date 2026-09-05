<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ForgotPasswordController extends Controller
{
    public function showPhoneForm()
    {
        return view('customer.auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:customers,phone',
        ], [
            'phone.exists' => 'We could not find an account with that phone number.',
        ]);

        $customer = Customer::where('phone', $request->phone)->first();
        $otp = rand(1000, 9999);

        // Store OTP in session valid for this phone number
        Session::put('password_reset_phone', $customer->phone);
        Session::put('password_reset_otp', $otp);
        Session::put('password_reset_expires_at', now()->addMinutes(10));

        // Attempt to send via WhatsApp
        try {
            $apiUrl   = env('WHATSAPP_API_URL');
            $apiToken = env('WHATSAPP_API_TOKEN');

            if ($apiUrl && $apiToken) {
                // Sending OTP via WhatsApp Meta API using a generic text message if allowed, 
                // or a template (assuming 'otp_verification' template exists)
                // For simplicity, we send a template since Meta strictly requires templates for business initiated messages.
                Http::timeout(5)
                    ->withToken($apiToken)
                    ->post($apiUrl, [
                        'messaging_product' => 'whatsapp',
                        'to'                => $customer->phone,
                        'type'              => 'template',
                        'template'          => [
                            'name'       => 'otp_verification',
                            'language'   => ['code' => 'en_US'],
                            'components' => [
                                [
                                    'type' => 'body',
                                    'parameters' => [
                                        ['type' => 'text', 'text' => (string) $otp]
                                    ]
                                ]
                            ]
                        ]
                    ]);
            } else {
                Log::info("No WhatsApp config. Mock OTP for {$customer->phone} is: {$otp}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send OTP WhatsApp: " . $e->getMessage());
            Log::info("Mock OTP for {$customer->phone} is: {$otp}");
        }

        return redirect()->route('customer.password.verifyForm')->with('success', 'A 4-digit reset code has been sent to your phone.');
    }

    public function showOtpForm()
    {
        if (!Session::has('password_reset_phone')) {
            return redirect()->route('customer.password.request');
        }

        return view('customer.auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4',
        ]);

        $expectedOtp = Session::get('password_reset_otp');
        $expiresAt = Session::get('password_reset_expires_at');

        if (!$expectedOtp || !$expiresAt || now()->greaterThan($expiresAt)) {
            Session::forget(['password_reset_phone', 'password_reset_otp', 'password_reset_expires_at']);
            return redirect()->route('customer.password.request')->withErrors(['phone' => 'OTP has expired. Please request a new one.']);
        }

        if ($request->otp != $expectedOtp) {
            return back()->withErrors(['otp' => 'Invalid OTP code. Please try again.']);
        }

        // OTP verified successfully, allow password reset
        Session::put('password_reset_verified', true);
        return redirect()->route('customer.password.resetForm');
    }

    public function showResetForm()
    {
        if (!Session::get('password_reset_verified')) {
            return redirect()->route('customer.password.request');
        }

        return view('customer.auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        if (!Session::get('password_reset_verified') || !Session::has('password_reset_phone')) {
            return redirect()->route('customer.password.request');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::where('phone', Session::get('password_reset_phone'))->first();
        if ($customer) {
            $customer->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // Clean up session
        Session::forget(['password_reset_phone', 'password_reset_otp', 'password_reset_expires_at', 'password_reset_verified']);

        return redirect()->route('customer.login')->with('success', 'Your password has been reset successfully. Please log in.');
    }
}
