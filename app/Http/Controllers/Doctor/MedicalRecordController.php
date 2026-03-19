<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\User;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MedicalRecordController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $doctor = auth()->user();

        $records = MedicalRecord::where('doctor_user_id', $doctor->id)
            ->with(['patient.profile', 'familyMember', 'prescription'])
            ->when($request->search, fn($q) => $q->whereHas('patient.profile', fn($q2) =>
                $q2->where('full_name', 'like', '%' . $request->search . '%')
            ))
            ->latest('visit_date')
            ->paginate(20)
            ->withQueryString();

        return view('doctor.records.index', compact('records'));
    }

    // ── Create form ───────────────────────────────────────────────────────────

    public function create(Request $request, int $patient)
    {
        $patient = User::where('id', $patient)->where('role', 'patient')
            ->with(['profile', 'familyMembers'])
            ->firstOrFail();

        // Pre-select family member if passed via query string
        $selectedMemberId = $request->query('member');

        $record  = null;
        $editing = false;
        return view('doctor.records.create', compact('patient', 'selectedMemberId', 'record', 'editing'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request, int $patient)
    {
        $patientUser = User::findOrFail($patient);

        $request->validate([
            'visit_date'        => ['required', 'date', 'before_or_equal:today'],
            'visit_type'        => ['required', 'in:consultation,follow_up,emergency,procedure,teleconsultation'],
            'chief_complaint'   => ['required', 'string', 'max:500'],
            'diagnosis'         => ['required', 'string', 'max:1000'],
            'examination_notes' => ['nullable', 'string'],
            'treatment_plan'    => ['nullable', 'string'],
            'doctor_notes'      => ['nullable', 'string'],
            'follow_up_date'    => ['nullable', 'date', 'after:visit_date'],
            'family_member_id'  => ['nullable', 'exists:family_members,id'],
            // Vitals
            'vitals.height'     => ['nullable', 'string', 'max:20'],
            'vitals.weight'     => ['nullable', 'numeric', 'min:0', 'max:300'],
            'vitals.bp'         => ['nullable', 'string', 'max:20'],
            'vitals.pulse'      => ['nullable', 'integer', 'min:0', 'max:300'],
            'vitals.temperature'=> ['nullable', 'numeric', 'min:30', 'max:45'],
            'vitals.spo2'       => ['nullable', 'integer', 'min:0', 'max:100'],
            // Attachments
            'attachments.*'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Build vitals array — only include non-null values
        $vitals = array_filter([
            'height'      => $request->input('vitals.height'),
            'weight'      => $request->input('vitals.weight'),
            'bp'          => $request->input('vitals.bp'),
            'pulse'       => $request->input('vitals.pulse'),
            'temperature' => $request->input('vitals.temperature'),
            'spo2'        => $request->input('vitals.spo2'),
        ]);

        // Handle file uploads
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("medical-records/{$patientUser->id}", 'public');
                $attachments[] = [
                    'name'       => $file->getClientOriginalName(),
                    'path'       => $path,
                    'type'       => $file->getMimeType(),
                    'size'       => $file->getSize(),
                    'uploaded_at'=> now()->toISOString(),
                ];
            }
        }

        $record = MedicalRecord::create([
            'patient_user_id'   => $patientUser->id,
            'family_member_id'  => $request->family_member_id ?: null,
            'doctor_user_id'    => auth()->id(),
            'visit_date'        => $request->visit_date,
            'visit_type'        => $request->visit_type,
            'chief_complaint'   => $request->chief_complaint,
            'diagnosis'         => $request->diagnosis,
            'examination_notes' => $request->examination_notes,
            'vitals'            => $vitals ?: null,
            'treatment_plan'    => $request->treatment_plan,
            'doctor_notes'      => $request->doctor_notes,
            'follow_up_date'    => $request->follow_up_date,
            'attachments'       => $attachments ?: null,
        ]);

        return redirect()
            ->route('doctor.records.show', $record)
            ->with('success', 'Medical record created successfully.');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(MedicalRecord $record)
    {
        $record->load([
            'patient.profile',
            'familyMember',
            'prescription.medicines',
        ]);

        return view('doctor.records.show', compact('record'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(MedicalRecord $record)
    {
        // Only the creating doctor can edit
        if ($record->doctor_user_id !== auth()->id()) {
            abort(403, 'Only the creating doctor can edit this record.');
        }

        $record->load(['patient.profile', 'patient.familyMembers', 'familyMember']);

        return view('doctor.records.create', [
            'patient'          => $record->patient,
            'record'           => $record,
            'editing'          => true,
            'selectedMemberId' => $record->family_member_id,
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, MedicalRecord $record)
    {
        if ($record->doctor_user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'visit_date'        => ['required', 'date', 'before_or_equal:today'],
            'visit_type'        => ['required', 'in:consultation,follow_up,emergency,procedure,teleconsultation'],
            'chief_complaint'   => ['required', 'string', 'max:500'],
            'diagnosis'         => ['required', 'string', 'max:1000'],
            'examination_notes' => ['nullable', 'string'],
            'treatment_plan'    => ['nullable', 'string'],
            'doctor_notes'      => ['nullable', 'string'],
            'follow_up_date'    => ['nullable', 'date', 'after:visit_date'],
            'vitals.height'     => ['nullable', 'string', 'max:20'],
            'vitals.weight'     => ['nullable', 'numeric'],
            'vitals.bp'         => ['nullable', 'string', 'max:20'],
            'vitals.pulse'      => ['nullable', 'integer'],
            'vitals.temperature'=> ['nullable', 'numeric'],
            'vitals.spo2'       => ['nullable', 'integer'],
        ]);

        $vitals = array_filter([
            'height'      => $request->input('vitals.height'),
            'weight'      => $request->input('vitals.weight'),
            'bp'          => $request->input('vitals.bp'),
            'pulse'       => $request->input('vitals.pulse'),
            'temperature' => $request->input('vitals.temperature'),
            'spo2'        => $request->input('vitals.spo2'),
        ]);

        $record->update([
            'visit_date'        => $request->visit_date,
            'visit_type'        => $request->visit_type,
            'chief_complaint'   => $request->chief_complaint,
            'diagnosis'         => $request->diagnosis,
            'examination_notes' => $request->examination_notes,
            'vitals'            => $vitals ?: null,
            'treatment_plan'    => $request->treatment_plan,
            'doctor_notes'      => $request->doctor_notes,
            'follow_up_date'    => $request->follow_up_date,
        ]);

        return redirect()
            ->route('doctor.records.show', $record)
            ->with('success', 'Record updated.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(MedicalRecord $record)
    {
        if ($record->doctor_user_id !== auth()->id()) {
            abort(403);
        }

        // Delete attached files
        if ($record->attachments) {
            foreach ($record->attachments as $att) {
                Storage::disk('public')->delete($att['path'] ?? '');
            }
        }

        $patientId = $record->patient_user_id;
        $record->delete();

        return redirect()
            ->route('doctor.patients.history', $patientId)
            ->with('success', 'Record deleted.');
    }

    // ── Upload attachment to existing record ──────────────────────────────────

    public function uploadAttachment(Request $request, MedicalRecord $record)
    {
        if ($record->doctor_user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'attachment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('attachment');
        $path = $file->store("medical-records/{$record->patient_user_id}", 'public');

        $existing = $record->attachments ?? [];
        $existing[] = [
            'name'        => $file->getClientOriginalName(),
            'path'        => $path,
            'type'        => $file->getMimeType(),
            'size'        => $file->getSize(),
            'uploaded_at' => now()->toISOString(),
        ];

        $record->update(['attachments' => $existing]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'attachment' => last($existing),
                'url' => Storage::disk('public')->url($path),
            ]);
        }

        return back()->with('success', 'File uploaded.');
    }

    // ── Delete single attachment ──────────────────────────────────────────────

    public function deleteAttachment(Request $request, MedicalRecord $record)
    {
        if ($record->doctor_user_id !== auth()->id()) abort(403);

        $index = (int) $request->input('index', -1);
        $attachments = $record->attachments ?? [];

        if (isset($attachments[$index])) {
            Storage::disk('public')->delete($attachments[$index]['path'] ?? '');
            array_splice($attachments, $index, 1);
            $record->update(['attachments' => $attachments ?: null]);
        }

        return back()->with('success', 'Attachment removed.');
    }
}
