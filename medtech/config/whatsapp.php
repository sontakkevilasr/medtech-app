<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Provider
    | Supported: "mock", "360dialog", "twilio", "wati"
    |--------------------------------------------------------------------------
    */
    'provider' => env('WHATSAPP_PROVIDER', 'mock'),

    'mock' => [
        'log' => true,  // Log messages to Laravel log instead of sending
    ],

    '360dialog' => [
        'api_key'  => env('DIALOG360_API_KEY'),
        'base_url' => 'https://waba.360dialog.io/v1',
        'namespace'=> env('DIALOG360_NAMESPACE'),
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from'  => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
    ],

    'wati' => [
        'api_endpoint' => env('WATI_API_ENDPOINT'),
        'access_token' => env('WATI_ACCESS_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Templates
    | Keys map to WhatsApp Business approved template names
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'appointment_reminder'   => 'medtech_apt_reminder',
        'prescription_ready'     => 'medtech_rx_ready',
        'vaccination_reminder'   => 'medtech_vaccine_reminder',
        'access_otp'             => 'medtech_access_otp',
        'medication_reminder'    => 'medtech_med_reminder',
        'visit_followup'         => 'medtech_followup',
        'pregnancy_milestone'    => 'medtech_pregnancy_tip',
    ],

];
