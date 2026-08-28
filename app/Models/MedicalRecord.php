<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_user_id', 'family_member_id', 'doctor_user_id',
        'visit_date', 'visit_type', 'chief_complaint', 'diagnosis',
        'examination_notes', 'vitals', 'treatment_plan',
        'doctor_notes', 'follow_up_date', 'attachments',
    ];

    protected function casts(): array
    {
        return [
            'visit_date'    => 'date',
            'follow_up_date'=> 'date',
            'vitals'        => 'array',
            'attachments'   => 'array',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }

    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForPatient($query, int $userId)
    {
        return $query->where('patient_user_id', $userId);
    }

    public function scopeByDoctor($query, int $doctorId)
    {
        return $query->where('doctor_user_id', $doctorId);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('visit_date');
    }
}
