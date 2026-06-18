<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PatientHistoryDocument extends Model
{
    protected $fillable = [
        'patient_user_id',
        'family_member_id',
        'title',
        'document_type',
        'notes',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'document_date',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'file_size'     => 'integer',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_user_id');
    }

    public function familyMember()
    {
        return $this->belongsTo(FamilyMember::class);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('patient.history.documents.download', $this->id);
    }

    public function getViewUrlAttribute(): string
    {
        return route('patient.history.documents.view', $this->id);
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }
    }
}
