<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'specialization', 'sub_specialization', 'registration_number',
        'registration_council', 'qualification', 'experience_years', 'clinic_name',
        'clinic_address', 'clinic_city', 'clinic_state', 'clinic_pincode',
        'consultation_fee', 'upi_id', 'upi_qr_image', 'languages_spoken',
        'available_slots', 'bio', 'is_premium', 'is_verified',
        'accept_online_appointments', 'whatsapp_number', 'whatsapp_country_code',
    ];

    protected function casts(): array
    {
        return [
            'languages_spoken'             => 'array',
            'available_slots'              => 'array',
            'is_premium'                   => 'boolean',
            'is_verified'                  => 'boolean',
            'verified_at'                  => 'datetime',
            'premium_expires_at'           => 'date',
            'accept_online_appointments'   => 'boolean',
            'consultation_fee'             => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timelineTemplates()
    {
        return $this->hasMany(TimelineTemplate::class, 'doctor_user_id', 'user_id');
    }

    public function scopePremium($query)   { return $query->where('is_premium', true); }
    public function scopeVerified($query)  { return $query->where('is_verified', true); }

    public function getWhatsappFullNumberAttribute(): string
    {
        return $this->whatsapp_country_code . $this->whatsapp_number;
    }
}
