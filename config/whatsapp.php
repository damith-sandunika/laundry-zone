<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API Settings
    |--------------------------------------------------------------------------
    | Uses Meta WhatsApp Cloud API (no Node.js required)
    | Get credentials from: https://developers.facebook.com/apps/
    */

    'enabled' => env('WHATSAPP_ENABLED', false),
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v19.0'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', ''),
];

