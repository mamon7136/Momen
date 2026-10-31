<?php

return [
    // whatsapp driver: "cloud" (Meta WhatsApp Cloud API) or "log"
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'token' => env('WHATSAPP_TOKEN'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    ],

    // sms driver for OTP: "http" (generic local aggregator for Zain/MTN/Sudani) or "log"
    'sms' => [
        'driver' => env('SMS_DRIVER', 'log'),
        'url' => env('SMS_URL'),
        'username' => env('SMS_USERNAME'),
        'password' => env('SMS_PASSWORD'),
        'sender' => env('SMS_SENDER', 'PLATFORM'),
    ],

    'syberpay' => [
        'base_url' => env('SYBERPAY_BASE_URL', 'https://syberpay.test.sybertechnology.com/syberpay'),
        'application_id' => env('SYBERPAY_APP_ID'),
        'service_id' => env('SYBERPAY_SERVICE_ID'),
        'salt' => env('SYBERPAY_SALT'),
    ],
];
