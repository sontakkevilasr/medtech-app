<?php namespace App\Enums;

enum AccessType: string 
{
    case Full        = 'full';
    case OtpRequired = 'otp_required';
}
