<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimelineTemplate extends Model
{
    use HasFactory;

    // Single source of truth for specialty ENUM — matches DB migration exactly
    const SPECIALTIES = [
        'obstetrics' => ['label' => 'Obstetrics / Pregnancy', 'icon' => '🤰', 'color' => '#c0737a', 'bg' => '#fce7ef'],
        'pediatrics' => ['label' => 'Paediatrics',            'icon' => '👶', 'color' => '#3d7a8a', 'bg' => '#e8f5f9'],
        'ivf'        => ['label' => 'IVF & Fertility',        'icon' => '🧬', 'color' => '#8a6aaa', 'bg' => '#f4f0fa'],
        'dental'     => ['label' => 'Dental & Ortho',         'icon' => '🦷', 'color' => '#3d7a6e', 'bg' => '#eef5f3'],
        'orthopedic' => ['label' => 'Orthopaedics',           'icon' => '🦴', 'color' => '#c98a3a', 'bg' => '#fdf5e8'],
        'oncology'   => ['label' => 'Oncology',               'icon' => '🎗️', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
        'custom'     => ['label' => 'Custom',                 'icon' => '📋', 'color' => '#4a3760', 'bg' => '#f4f0fa'],
    ];

    protected $fillable = [
        'doctor_user_id', 'specialty_type', 'title', 'description',
        'total_duration_days', 'duration_unit', 'is_system_template', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system_template' => 'boolean',
            'is_active'          => 'boolean',
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }

    public function milestones()
    {
        return $this->hasMany(TimelineMilestone::class, 'template_id')
                    ->orderBy('offset_value');
    }

    public function patientTimelines()
    {
        return $this->hasMany(PatientTimeline::class, 'template_id');
    }

    public function scopeActive($query)  { return $query->where('is_active', true); }
    public function scopeSystem($query)  { return $query->where('is_system_template', true); }
}


class TimelineMilestone extends Model
{
    protected $fillable = [
        'template_id', 'title', 'description', 'offset_value', 'offset_unit',
        'milestone_type', 'precautions', 'diet_advice', 'exercise_advice',
        'reminder_days_before', 'icon', 'color', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'reminder_days_before' => 'array',
        ];
    }

    public function template()
    {
        return $this->belongsTo(TimelineTemplate::class);
    }

    /** Calculate the actual date for this milestone given a start date */
    public function calculateDate(\Carbon\Carbon $startDate): \Carbon\Carbon
    {
        return match($this->offset_unit) {
            'day'   => $startDate->copy()->addDays($this->offset_value),
            'week'  => $startDate->copy()->addWeeks($this->offset_value),
            'month' => $startDate->copy()->addMonths($this->offset_value),
            default => $startDate->copy()->addDays($this->offset_value),
        };
    }
}


class PatientTimeline extends Model
{
    protected $fillable = [
        'template_id', 'patient_user_id', 'family_member_id',
        'assigned_by_doctor_id', 'start_date', 'expected_end_date',
        'custom_notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date'        => 'date',
            'expected_end_date' => 'date',
            'custom_notes'      => 'array',
            'is_active'         => 'boolean',
        ];
    }

    public function template()
    {
        return $this->belongsTo(TimelineTemplate::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function assignedByDoctor()
    {
        return $this->belongsTo(User::class, 'assigned_by_doctor_id');
    }

    /** Return milestones with their computed real dates */
    public function getMilestonesWithDates(): \Illuminate\Support\Collection
    {
        return $this->template->milestones->map(function ($milestone) {
            $milestone->actual_date = $milestone->calculateDate($this->start_date);
            $milestone->is_past     = $milestone->actual_date->isPast();
            $milestone->is_today    = $milestone->actual_date->isToday();
            $milestone->days_away   = (int) now()->diffInDays($milestone->actual_date, false);
            return $milestone;
        });
    }
}
