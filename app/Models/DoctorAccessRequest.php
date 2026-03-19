<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorAccessRequest extends Model
{
    protected $fillable = [
        'doctor_user_id', 'patient_user_id', 'family_member_id',
        'patient_identifier', 'identifier_type', 'status',
        'otp', 'otp_expires_at', 'approved_at', 'access_expires_at',
    ];

    protected $hidden = ['otp'];

    protected function casts(): array
    {
        return [
            'otp_expires_at'    => 'datetime',
            'approved_at'       => 'datetime',
            'access_expires_at' => 'datetime',
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isDenied(): bool    { return $this->status === 'denied'; }

    public function isAccessActive(): bool
    {
        return $this->isApproved()
            && $this->access_expires_at
            && now()->lessThanOrEqualTo($this->access_expires_at);
    }

    public function isOtpValid(string $otp): bool
    {
        return $this->otp === $otp
            && $this->otp_expires_at
            && now()->lessThanOrEqualTo($this->otp_expires_at);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
                     ->where('access_expires_at', '>', now());
    }
}
