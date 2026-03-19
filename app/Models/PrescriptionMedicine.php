<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionMedicine extends Model
{
    protected $fillable = [
        'prescription_id', 'medicine_name', 'generic_name',
        'dosage', 'form', 'frequency', 'duration_days',
        'timing', 'special_instructions', 'sort_order',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /** Human-friendly timing label */
    public function getTimingLabelAttribute(): string
    {
        return match($this->timing) {
            'before_food' => 'Before Food',
            'after_food'  => 'After Food',
            'with_food'   => 'With Food',
            default       => 'Any Time',
        };
    }
}
