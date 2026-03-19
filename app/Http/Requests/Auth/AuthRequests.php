<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

// ─── LoginRequest ─────────────────────────────────────────────────────────────

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'mobile_number' => ['required', 'digits_between:7,15'],
            'country_code'  => ['required', 'string', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile_number.required'      => 'Please enter your mobile number.',
            'mobile_number.digits_between'=> 'Mobile number must be 7–15 digits.',
            'country_code.required'       => 'Please select your country code.',
        ];
    }
}


// ─── PasswordLoginRequest ─────────────────────────────────────────────────────

class PasswordLoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'mobile_number' => ['required', 'digits_between:7,15'],
            'country_code'  => ['required', 'string', 'max:5'],
            'password'      => ['required', 'string', 'min:6'],
        ];
    }
}


// ─── OtpVerifyRequest ─────────────────────────────────────────────────────────

class OtpVerifyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'otp' => ['required', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'otp.required' => 'Please enter the OTP.',
            'otp.digits'   => 'OTP must be exactly 6 digits.',
        ];
    }
}


// ─── PasswordSetupRequest ─────────────────────────────────────────────────────

class PasswordSetupRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'password.min'    => 'Password must be at least 8 characters.',
            'password.regex'  => 'Password must contain uppercase, lowercase, and a number.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}


// ─── ProfileSetupRequest ──────────────────────────────────────────────────────

class ProfileSetupRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'full_name'   => ['required', 'string', 'min:2', 'max:100'],
            'dob'         => ['nullable', 'date', 'before:today'],
            'gender'      => ['nullable', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'city'        => ['nullable', 'string', 'max:100'],
            'state'       => ['nullable', 'string', 'max:100'],
        ];
    }
}
