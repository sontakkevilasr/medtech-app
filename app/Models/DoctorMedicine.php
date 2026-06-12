<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorMedicine extends Model
{
    protected $fillable = [
        'doctor_user_id',
        'medicine_name',
        'generic_name',
        'form',
        'dosage',
        'frequency',
        'timing',
        'duration_days',
        'special_instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'     => 'boolean',
            'duration_days' => 'integer',
        ];
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
