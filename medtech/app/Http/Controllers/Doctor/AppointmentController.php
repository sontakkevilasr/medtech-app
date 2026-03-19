<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(private WhatsAppService $whatsApp) {}

    // ── List ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $doctor = auth()->user();
        $tab    = $request->get('tab', 'upcoming');
        $date   = $request->get('date');

        $query = Appointment::where('doctor_user_id', $doctor->id)
            ->with(['patient.profile', 'familyMember']);

        if ($date) {
            $query->whereDate('slot_datetime', $date);
        } else {
            match ($tab) {
                'upcoming'  => $query->where('slot_datetime','>', now())->whereNotIn('status',['cancelled'])->orderBy('slot_datetime'),
                'today'     => $query->whereDate('slot_datetime', today())->orderBy('slot_datetime'),
                'past'      => $query->where('slot_datetime','<', now())->whereNotIn('status',['cancelled'])->orderByDesc('slot_datetime'),
                'cancelled' => $query->where('status','cancelled')->orderByDesc('slot_datetime'),
                default     => $query->orderBy('slot_datetime'),
            };
        }

        $appointments = $query->paginate(15)->withQueryString();

        $counts = [
            'today'    => Appointment::where('doctor_user_id',$doctor->id)->whereDate('slot_datetime',today())->whereNotIn('status',['cancelled'])->count(),
            'upcoming' => Appointment::where('doctor_user_id',$doctor->id)->where('slot_datetime','>',now())->whereNotIn('status',['cancelled'])->count(),
            'past'     => Appointment::where('doctor_user_id',$doctor->id)->where('slot_datetime','<',now())->whereNotIn('status',['cancelled'])->count(),
        ];

        return view('doctor.appointments.index', compact('appointments', 'tab', 'counts', 'date'));
    }

    // ── Calendar ─────────────────────────────────────────────────────────────

    public function calendar(Request $request)
    {
        $doctor = auth()->user();
        $month  = $request->get('month', today()->format('Y-m'));
        [$year, $mon] = explode('-', $month);

        $start = Carbon::create($year, $mon, 1)->startOfMonth();
        $end   = Carbon::create($year, $mon, 1)->endOfMonth();

        // All appointments in the month, grouped by date
        $appointments = Appointment::where('doctor_user_id', $doctor->id)
            ->whereBetween('slot_datetime', [$start, $end])
            ->whereNotIn('status', ['cancelled'])
            ->with(['patient.profile', 'familyMember'])
            ->orderBy('slot_datetime')
            ->get()
            ->groupBy(fn($a) => $a->slot_datetime->format('Y-m-d'));

        return view('doctor.appointments.calendar', compact('appointments', 'month', 'start', 'end'));
    }

    // ── Today ────────────────────────────────────────────────────────────────

    public function today()
    {
        $doctor = auth()->user();
        $appointments = Appointment::where('doctor_user_id', $doctor->id)
            ->whereDate('slot_datetime', today())
            ->whereNotIn('status', ['cancelled'])
            ->with(['patient.profile', 'familyMember'])
            ->orderBy('slot_datetime')
            ->get();

        return view('doctor.appointments.today', compact('appointments'));
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function show(Appointment $appointment)
    {
        $this->gate($appointment);
        $appointment->load(['patient.profile', 'doctor.profile', 'doctor.doctorProfile', 'familyMember']);
        return view('doctor.appointments.show', compact('appointment'));
    }

    // ── Confirm ──────────────────────────────────────────────────────────────

    public function confirm(Request $request, Appointment $appointment)
    {
        $this->gate($appointment);
        $appointment->update(['status' => 'confirmed']);

        try {
            $this->whatsApp->sendAppointmentConfirmation(
                $appointment->load(['doctor.profile', 'patient.profile'])
            );
        } catch (\Exception) {}

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => 'confirmed']);
        }

        return back()->with('success', 'Appointment confirmed. Patient notified via WhatsApp.');
    }

    // ── Complete ─────────────────────────────────────────────────────────────

    public function complete(Request $request, Appointment $appointment)
    {
        $this->gate($appointment);
        $appointment->update(['status' => 'completed']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => 'completed']);
        }

        return back()->with('success', 'Appointment marked as completed.');
    }

    // ── Cancel ───────────────────────────────────────────────────────────────

    public function cancel(Request $request, Appointment $appointment)
    {
        $this->gate($appointment);
        $appointment->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Cancelled by doctor',
        ]);

        try {
            $this->whatsApp->sendAppointmentCancellation(
                $appointment->load(['doctor.profile', 'patient.profile'])
            );
        } catch (\Exception) {}

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Appointment cancelled.');
    }

    // ── Send reminder ────────────────────────────────────────────────────────

    public function sendReminder(Appointment $appointment)
    {
        $this->gate($appointment);
        try {
            $this->whatsApp->sendAppointmentReminder(
                $appointment->load(['doctor.profile', 'patient.profile'])
            );
            return back()->with('success', 'Reminder sent to patient.');
        } catch (\Exception $e) {
            return back()->with('error', 'Could not send reminder: ' . $e->getMessage());
        }
    }

    // ── Manage availability slots ─────────────────────────────────────────────

    public function manageSlots()
    {
        $doctor  = auth()->user();
        $profile = $doctor->doctorProfile;
        $slots   = $profile?->available_slots ?? $this->defaultSlots();

        return view('doctor.appointments.slots', compact('slots'));
    }

    public function saveSlots(Request $request)
    {
        $request->validate([
            'slots'                     => ['required', 'array'],
            'slots.*.enabled'           => ['sometimes', 'boolean'],
            'slots.*.blocks'            => ['sometimes', 'array'],
            'slots.*.blocks.*.start'    => ['sometimes', 'date_format:H:i'],
            'slots.*.blocks.*.end'      => ['sometimes', 'date_format:H:i'],
        ]);

        $doctor  = auth()->user();
        $profile = $doctor->doctorProfile;

        $days  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $saved = [];

        foreach ($days as $day) {
            $dayData = $request->input("slots.{$day}", []);
            $enabled = !empty($dayData['enabled']);
            $blocks  = [];

            if ($enabled && !empty($dayData['blocks'])) {
                foreach ($dayData['blocks'] as $block) {
                    if (!empty($block['start']) && !empty($block['end']) && $block['start'] < $block['end']) {
                        $blocks[] = ['start' => $block['start'], 'end' => $block['end']];
                    }
                }
            }

            $saved[$day] = $blocks;
        }

        $profile->update(['available_slots' => $saved]);

        return back()->with('success', 'Availability updated successfully.');
    }

    // ── AJAX: available slots (for doctor's own view) ─────────────────────────

    public function availableSlots(Request $request)
    {
        $request->validate(['date' => ['required', 'date']]);
        $doctor  = auth()->user();
        $profile = $doctor->doctorProfile;
        $date    = Carbon::parse($request->date);
        $dayName = strtolower($date->format('l'));

        $slots    = $profile->available_slots ?? [];
        $daySlots = $slots[$dayName] ?? [];
        $duration = config('medtech.appointment.default_duration', 15);

        $allTimes = [];
        foreach ($daySlots as $block) {
            $start  = Carbon::parse($date->format('Y-m-d') . ' ' . $block['start']);
            $end    = Carbon::parse($date->format('Y-m-d') . ' ' . $block['end']);
            $cursor = $start->copy();
            while ($cursor->copy()->addMinutes($duration)->lte($end)) {
                $allTimes[] = $cursor->format('H:i');
                $cursor->addMinutes($duration);
            }
        }

        $booked = Appointment::where('doctor_user_id', $doctor->id)
            ->whereDate('slot_datetime', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('slot_datetime')
            ->map(fn($dt) => $dt->format('H:i'))
            ->toArray();

        return response()->json([
            'all'    => $allTimes,
            'booked' => $booked,
            'date'   => $request->date,
        ]);
    }

    private function gate(Appointment $a): void
    {
        if ($a->doctor_user_id !== auth()->id()) abort(403);
    }

    private function defaultSlots(): array
    {
        $working = ['start' => '09:00', 'end' => '13:00'];
        $slots   = [];
        foreach (['monday','tuesday','wednesday','thursday','friday'] as $d) {
            $slots[$d] = [$working];
        }
        foreach (['saturday','sunday'] as $d) {
            $slots[$d] = [];
        }
        return $slots;
    }
}
