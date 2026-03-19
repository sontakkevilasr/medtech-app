<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $patient  = auth()->user()->load('familyMembers');
        $memberId = $request->get('member');
        $type     = $request->get('type');

        $records = MedicalRecord::where('patient_user_id', $patient->id)
            ->when($memberId,
                fn($q) => $q->where('family_member_id', $memberId),
                fn($q) => $q->whereNull('family_member_id')
            )
            ->when($type, fn($q) => $q->where('visit_type', $type))
            ->with(['doctor.profile', 'doctor.doctorProfile', 'familyMember'])
            ->recent()
            ->paginate(12)
            ->withQueryString();

        return view('patient.records.index', compact('patient', 'records', 'memberId', 'type'));
    }

    public function show(MedicalRecord $record)
    {
        // Patient can only view their own records
        if ($record->patient_user_id !== auth()->id()) abort(403);

        $record->load([
            'doctor.profile',
            'doctor.doctorProfile',
            'familyMember',
            'prescription.medicines',
        ]);

        return view('patient.records.show', compact('record'));
    }
}
