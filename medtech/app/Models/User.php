<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'mobile_number', 'country_code', 'email', 'password',
        'role', 'otp', 'otp_expires_at', 'is_verified', 'is_active',
        'preferred_language', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp',
    ];

    protected function casts(): array
    {
        return [
            'password'        => 'hashed',
            'otp_expires_at'  => 'datetime',
            'last_login_at'   => 'datetime',
            'is_verified'     => 'boolean',
            'is_active'       => 'boolean',
        ];
    }

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function isDoctor(): bool   { return $this->role === 'doctor'; }
    public function isPatient(): bool  { return $this->role === 'patient'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }

    public function getFullMobileAttribute(): string
    {
        return $this->country_code . $this->mobile_number;
    }

    public function isOtpValid(string $otp): bool
    {
        return $this->otp === $otp
            && $this->otp_expires_at
            && now()->lessThanOrEqualTo($this->otp_expires_at);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    /** Family members where this user is the primary account holder */
    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class, 'primary_user_id');
    }

    /** Medical records as a patient */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_user_id');
    }

    /** Medical records created by this doctor */
    public function doctorMedicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_user_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_user_id');
    }

    public function doctorPrescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_user_id');
    }

    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_user_id');
    }

    public function accessPermission()
    {
        return $this->hasOne(PatientAccessPermission::class, 'patient_user_id');
    }

    public function accessRequests()
    {
        return $this->hasMany(DoctorAccessRequest::class, 'doctor_user_id');
    }

    public function healthLogs()
    {
        return $this->hasMany(HealthLog::class, 'patient_user_id');
    }

    public function medicationReminders()
    {
        return $this->hasMany(MedicationReminder::class, 'patient_user_id');
    }

    public function timelines()
    {
        return $this->hasMany(PatientTimeline::class, 'patient_user_id');
    }

    public function doctorTimelines()
    {
        return $this->hasMany(PatientTimeline::class, 'assigned_by_doctor_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(DoctorSubscription::class, 'doctor_user_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(DoctorSubscription::class, 'doctor_user_id')
                    ->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeDoctors($query)     { return $query->where('role', 'doctor'); }
    public function scopePatients($query)    { return $query->where('role', 'patient'); }
    public function scopeVerified($query)    { return $query->where('is_verified', true); }
    public function scopeActive($query)      { return $query->where('is_active', true); }
}
