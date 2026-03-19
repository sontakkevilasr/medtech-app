<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicationReminder;
use App\Models\Prescription;
use Illuminate\Http\Request;

class MedicationReminderController extends Controller
{
    public function index()
    {
        $patient = auth()->user()->load('familyMembers');

        $active = MedicationReminder::where('patient_user_id', $patient->id)
            ->active()
            ->with(['familyMember', 'prescription'])
            ->orderBy('start_date')
            ->get();

        $inactive = MedicationReminder::where('patient_user_id', $patient->id)
            ->where(fn($q) => $q->where('is_active', false)
                                ->orWhere('end_date', '<', today()))
            ->with(['familyMember', 'prescription'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        // Upcoming reminders today (for the "due today" strip)
        $dueToday = $active->filter(fn($r) => $r->isActive())
            ->sortBy(fn($r) => min($r->reminder_times ?? []))
            ->values();

        // Recent prescriptions for quick-add
        $prescriptions = Prescription::where('patient_user_id', $patient->id)
            ->with('medicines')
            ->whereNull('cancelled_at')
            ->orderByDesc('prescribed_date')
            ->limit(5)
            ->get();

        return view('patient.reminders.index', compact(
            'patient', 'active', 'inactive', 'dueToday', 'prescriptions'
        ));
    }

    public function create()
    {
        return redirect()->route('patient.reminders.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_name'     => ['required', 'string', 'max:120'],
            'dosage'            => ['required', 'string', 'max:60'],
            'reminder_times'    => ['required', 'array', 'min:1'],
            'reminder_times.*'  => ['required', 'date_format:H:i'],
            'start_date'        => ['required', 'date'],
            'end_date'          => ['nullable', 'date', 'after:start_date'],
            'channel'           => ['required', 'in:whatsapp,sms,both'],
            'family_member_id'  => ['nullable', 'exists:family_members,id'],
            'prescription_id'   => ['nullable', 'exists:prescriptions,id'],
        ]);

        // Validate family member belongs to patient
        if ($request->family_member_id) {
            auth()->user()->familyMembers()->findOrFail($request->family_member_id);
        }

        MedicationReminder::create([
            'patient_user_id'  => auth()->id(),
            'family_member_id' => $request->family_member_id ?: null,
            'prescription_id'  => $request->prescription_id ?: null,
            'medicine_name'    => $request->medicine_name,
            'dosage'           => $request->dosage,
            'reminder_times'   => $request->reminder_times,
            'start_date'       => $request->start_date,
            'end_date'         => $request->end_date,
            'channel'          => $request->channel,
            'is_active'        => true,
        ]);

        return back()->with('success', "Reminder set for {$request->medicine_name}.");
    }

    public function update(Request $request, MedicationReminder $reminder)
    {
        if ($reminder->patient_user_id !== auth()->id()) abort(403);

        $request->validate([
            'reminder_times'   => ['sometimes', 'array'],
            'reminder_times.*' => ['date_format:H:i'],
            'end_date'         => ['nullable', 'date'],
            'channel'          => ['sometimes', 'in:whatsapp,sms,both'],
        ]);

        $reminder->update($request->only('reminder_times', 'end_date', 'channel', 'dosage'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Reminder updated.');
    }

    public function destroy(MedicationReminder $reminder)
    {
        if ($reminder->patient_user_id !== auth()->id()) abort(403);
        $reminder->delete();

        return back()->with('success', 'Reminder removed.');
    }

    public function toggle(Request $request, MedicationReminder $reminder)
    {
        if ($reminder->patient_user_id !== auth()->id()) abort(403);

        $reminder->update(['is_active' => ! $reminder->is_active]);

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'is_active' => $reminder->is_active,
            ]);
        }

        return back()->with('success',
            $reminder->is_active ? 'Reminder activated.' : 'Reminder paused.'
        );
    }
}
