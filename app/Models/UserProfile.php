<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'full_name', 'dob', 'gender', 'profile_photo',
        'aadhaar_number', 'blood_group', 'address', 'city',
        'state', 'pincode', 'emergency_contact_name', 'emergency_contact_number',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    // Auto-encrypt Aadhaar on set, decrypt on get
    public function setAadhaarNumberAttribute($value): void
    {
        $this->attributes['aadhaar_number'] = $value ? encrypt($value) : null;
    }

    public function getAadhaarNumberAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->dob ? $this->dob->age : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
