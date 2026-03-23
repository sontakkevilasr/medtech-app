<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    */

    // How many digits the OTP should be
    'length' => env('OTP_LENGTH', 6),

    // How many minutes before OTP expires
    'expires_in' => env('OTP_EXPIRES_MINUTES', 10),

    // Max attempts before lockout
    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),

    // Lockout duration in minutes after max attempts exceeded
    'lockout_minutes' => env('OTP_LOCKOUT_MINUTES', 30),

    // How many seconds before a resend is allowed
    'resend_cooldown_seconds' => env('OTP_RESEND_COOLDOWN', 60),

    /*
    |--------------------------------------------------------------------------
    | SMS Provider
    | Supported: "mock", "msg91", "fast2sms", "twilio"
    |--------------------------------------------------------------------------
    */
    'provider' => env('OTP_PROVIDER', 'mock'),

    'mock' => [
        // In mock mode, OTP is always this value (for dev/testing)
        'fixed_otp' => env('OTP_MOCK_VALUE', '123456'),
        // Log OTP to Laravel log instead of sending SMS
        'log'       => true,
    ],

    'msg91' => [
        'auth_key'    => env('MSG91_AUTH_KEY'),
        'template_id' => env('MSG91_TEMPLATE_ID'),
        'sender_id'   => env('MSG91_SENDER_ID', 'MEDTCH'),
    ],

    'fast2sms' => [
        'api_key'     => env('FAST2SMS_API_KEY'),
        'sender_id'   => env('FAST2SMS_SENDER_ID', 'MEDTCH'),
        'message'     => 'Your Naumah Clinic OTP is {otp}. Valid for {minutes} minutes. Do not share.',
    ],

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

];
