<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'primary_user_id', 'sub_id', 'full_name', 'dob', 'gender',
        'relation', 'blood_group', 'profile_photo', 'aadhaar_number',
        'is_delinked', 'linked_mobile', 'linked_country_code',
        'linked_user_id', 'delinked_at',
    ];

    protected function casts(): array
    {
        return [
            'dob'          => 'date',
            'is_delinked'  => 'boolean',
            'delinked_at'  => 'datetime',
        ];
    }

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

    // ─── Relationships ───────────────────────────────────────────────────────

    public function primaryUser()
    {
        return $this->belongsTo(User::class, 'primary_user_id');
    }

    /** User account this member was delinked to */
    public function linkedUser()
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function accessPermission()
    {
        return $this->hasOne(PatientAccessPermission::class);
    }

    public function healthLogs()
    {
        return $this->hasMany(HealthLog::class);
    }

    public function timelines()
    {
        return $this->hasMany(PatientTimeline::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)    { return $query->where('is_delinked', false); }
    public function scopeDelinked($query)  { return $query->where('is_delinked', true); }
}
