<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sub-ID Format
    |--------------------------------------------------------------------------
    | Format: PREFIX-USERID-SUFFIX
    | Example: MED-00123-A, MED-00123-B
    */
    'sub_id' => [
        'prefix'         => env('SUB_ID_PREFIX', 'MED'),
        'padding'         => 5,          // zero-pad user ID to 5 digits
        'suffix_chars'    => 'ABCDEFGHJKLMNPQRSTUVWXYZ', // no I/O to avoid confusion
    ],

    /*
    |--------------------------------------------------------------------------
    | Doctor Access Session
    |--------------------------------------------------------------------------
    */
    'access' => [
        // How long (hours) an approved access grant remains valid per session
        'session_hours'    => env('DOCTOR_ACCESS_HOURS', 8),
        // How many minutes before access OTP expires
        'otp_expires'      => env('ACCESS_OTP_EXPIRES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prescription
    |--------------------------------------------------------------------------
    */
    'prescription' => [
        'number_prefix'   => 'RX',
        'pdf_disk'        => 'local',
        'pdf_path'        => 'prescriptions/pdfs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Appointment
    |--------------------------------------------------------------------------
    */
    'appointment' => [
        'number_prefix'         => 'APT',
        'reminder_24h_before'   => 24,   // hours
        'reminder_1h_before'    => 60,   // minutes
        'cancellation_window'   => 2,    // hours before slot (can't cancel after)
        'default_duration'      => 15,   // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    */
    'languages' => [
        'en' => 'English',
        'hi' => 'हिंदी',
        'mr' => 'मराठी',
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Log Ranges (for chart warnings / colour coding)
    |--------------------------------------------------------------------------
    */
    'health_ranges' => [
        'bp_systolic'  => ['normal' => [90, 120],  'warning' => [121, 140], 'danger' => [141, 999]],
        'bp_diastolic' => ['normal' => [60, 80],   'warning' => [81, 90],  'danger' => [91, 999]],
        'sugar_fasting'=> ['normal' => [70, 100],  'warning' => [101, 125],'danger' => [126, 999]],
        'sugar_pp'     => ['normal' => [70, 140],  'warning' => [141, 199],'danger' => [200, 999]],
        'oxygen'       => ['normal' => [95, 100],  'warning' => [90, 94],  'danger' => [0, 89]],
        'pulse'        => ['normal' => [60, 100],  'warning' => [101, 120],'danger' => [121, 999]],
        'temperature'  => ['normal' => [36.1,37.2],'warning' => [37.3,38.0],'danger'=> [38.1, 99]],
    ],

];
