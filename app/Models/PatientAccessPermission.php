<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientAccessPermission extends Model
{
    protected $fillable = [
        'patient_user_id', 'family_member_id', 'access_type',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function isFullAccess(): bool
    {
        return $this->access_type === 'full';
    }

    public function isOtpRequired(): bool
    {
        return $this->access_type === 'otp_required';
    }
}
