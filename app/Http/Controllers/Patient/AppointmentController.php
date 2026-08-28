<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use App\Models\DoctorProfile;
use App\Services\ReminderService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private ReminderService $reminder,
        private WhatsAppService $whatsApp,
    ) {}

    // ── My appointments list ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $patient = auth()->user();
        $tab     = $request->get('tab', 'upcoming');

        $query = Appointment::where('patient_user_id', $patient->id)
            ->with(['doctor.profile', 'doctor.doctorProfile', 'familyMember']);

        match ($tab) {
            'upcoming' => $query->where('slot_datetime', '>', now())
                                ->whereNotIn('status', ['cancelled'])
                                ->orderBy('slot_datetime'),
            'past'     => $query->where('slot_datetime', '<', now())
                                ->whereNotIn('status', ['cancelled'])
                                ->orderByDesc('slot_datetime'),
            'cancelled'=> $query->where('status', 'cancelled')
                                ->orderByDesc('slot_datetime'),
            default    => $query->orderByDesc('slot_datetime'),
        };

        $appointments = $query->paginate(10)->withQueryString();

        $upcomingCount  = Appointment::where('patient_user_id', $patient->id)->where('slot_datetime','>', now())->whereNotIn('status',['cancelled'])->count();
        $pastCount      = Appointment::where('patient_user_id', $patient->id)->where('slot_datetime','<', now())->whereNotIn('status',['cancelled'])->count();
        $cancelledCount = Appointment::where('patient_user_id', $patient->id)->where('status','cancelled')->count();

        return view('patient.appointments.index', compact(
            'appointments', 'tab',
            'upcomingCount', 'pastCount', 'cancelledCount'
        ));
    }

    // ── Step 1: Pick a doctor ────────────────────────────────────────────────

    public function showDoctors(Request $request)
    {
        $patient    = auth()->user();
        $search     = $request->get('q');
        $specialization = $request->get('spec');

        // My existing doctors first (with active access)
        $myDoctorIds = \App\Models\DoctorAccessRequest::where('patient_user_id', $patient->id)
            ->active()
            ->pluck('doctor_user_id');

        $query = User::where('role', 'doctor')
            ->where('is_active', true)
            ->with(['profile', 'doctorProfile'])
            ->whereHas('doctorProfile', fn($q) => $q->where('is_verified', true));

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('profile', fn($qq) => $qq->where('full_name', 'like', "%{$search}%"))
                  ->orWhereHas('doctorProfile', fn($qq) =>
                      $qq->where('specialization', 'like', "%{$search}%")
                         ->orWhere('clinic_name', 'like', "%{$search}%"));
            });
        }

        if ($specialization) {
            $query->whereHas('doctorProfile', fn($q) =>
                $q->where('specialization', 'like', "%{$specialization}%"));
        }

        $allDoctors = $query->get()->map(function ($doc) use ($myDoctorIds) {
            $doc->is_my_doctor = $myDoctorIds->contains($doc->id);
            return $doc;
        })->sortByDesc('is_my_doctor')->values();

        // My doctors section (top)
        $myDoctors  = $allDoctors->filter(fn($d) => $d->is_my_doctor)->values();
        $otherDoctors = $allDoctors->filter(fn($d) => !$d->is_my_doctor)->values();

        // All specializations for filter chips
        $specializations = DoctorProfile::whereNotNull('specialization')
            ->distinct()->pluck('specialization')->sort()->values();

        return view('patient.appointments.book-doctor', compact(
            'myDoctors', 'otherDoctors', 'specializations', 'search', 'specialization'
        ));
    }

    // ── Step 2: Pick a slot (AJAX + full page) ───────────────────────────────

    public function showSlots(Request $request, int $doctor)
    {
        $doctor = User::where('id', $doctor)->where('role', 'doctor')
            ->with(['profile', 'doctorProfile'])
            ->firstOrFail();

        $patient      = auth()->user();
        $familyMember = null;

        return view('patient.appointments.book-slots', compact('doctor', 'patient', 'familyMember'));
    }

    public function showSlotsForMember(Request $request, int $doctor, int $member)
    {
        $doctor = User::where('id', $doctor)->where('role', 'doctor')
            ->with(['profile', 'doctorProfile'])->firstOrFail();

        $familyMember = auth()->user()->familyMembers()->where('id', $member)->firstOrFail();

        return view('patient.appointments.book-slots', compact('doctor', 'familyMember')
            + ['patient' => auth()->user()]);
    }

    // ── AJAX: Available slots for a given date ───────────────────────────────
    // GET /patient/appointments/book/{doctor}/slots?date=2025-03-15

    public function availableSlotsForDate(Request $request, int $doctorId)
    {
        $request->validate(['date' => ['required', 'date', 'after_or_equal:today']]);

        $doctor  = User::findOrFail($doctorId);
        $profile = $doctor->doctorProfile;
        $date    = Carbon::parse($request->date);
        $dayName = strtolower($date->format('l')); // monday, tuesday…

        $slots = $profile->available_slots ?? [];
        $daySlots = $slots[$dayName] ?? []; // [["start":"09:00","end":"13:00"],...]

        if (empty($daySlots)) {
            return response()->json(['available' => [], 'date' => $request->date]);
        }

        $duration = config('medtech.appointment.default_duration', 15);

        // Generate all slot times
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

        // Remove already-booked slots
        $booked = Appointment::where('doctor_user_id', $doctorId)
            ->whereDate('slot_datetime', $date)
            ->whereNotIn('status', ['cancelled'])
            ->pluck('slot_datetime')
            ->map(fn($dt) => $dt->format('H:i'))
            ->toArray();

        $available = array_values(array_filter($allTimes, function ($t) use ($booked, $date) {
            if (in_array($t, $booked)) return false;
            // Remove past slots for today
            if ($date->isToday() && Carbon::parse($date->format('Y-m-d') . ' ' . $t)->isPast()) return false;
            return true;
        }));

        return response()->json([
            'available'  => $available,
            'booked'     => $booked,
            'date'       => $request->date,
            'duration'   => $duration,
            'fee'        => $profile->consultation_fee,
        ]);
    }

    // ── AJAX: Available dates for a month ────────────────────────────────────
    // GET /patient/appointments/book/{doctor}/dates?month=2025-03

    public function availableDatesForMonth(Request $request, int $doctorId)
    {
        $request->validate(['month' => ['required', 'date_format:Y-m']]);

        $doctor  = User::findOrFail($doctorId);
        $profile = $doctor->doctorProfile;
        $slots   = $profile->available_slots ?? [];

        if (empty($slots)) {
            return response()->json(['available_dates' => []]);
        }

        $availableDays = array_keys(array_filter($slots, fn($s) => !empty($s)));

        [$year, $month] = explode('-', $request->month);
        $start = Carbon::create($year, $month, 1)->max(today());
        $end   = Carbon::create($year, $month, 1)->endOfMonth();

        $availableDates = [];
        $period = CarbonPeriod::create($start, $end);
        foreach ($period as $day) {
            if (in_array(strtolower($day->format('l')), $availableDays)) {
                $availableDates[] = $day->format('Y-m-d');
            }
        }

        return response()->json(['available_dates' => $availableDates]);
    }

    // ── Step 3: Store booking ────────────────────────────────────────────────

    public function store(Request $request, int $doctorId)
    {
        return $this->bookAppointment($request, $doctorId, null);
    }

    public function storeForMember(Request $request, int $doctorId, int $memberId)
    {
        $member = auth()->user()->familyMembers()->where('id', $memberId)->firstOrFail();
        return $this->bookAppointment($request, $doctorId, $member);
    }

    private function bookAppointment(Request $request, int $doctorId, $familyMember)
    {
        $request->validate([
            'slot_date'   => ['required', 'date', 'after_or_equal:today'],
            'slot_time'   => ['required', 'date_format:H:i'],
            'type'        => ['required', 'in:consultation,follow_up,emergency'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ]);

        $doctor      = User::findOrFail($doctorId);
        $slotDt      = Carbon::parse($request->slot_date . ' ' . $request->slot_time);

        // Double-book guard
        $conflict = Appointment::where('doctor_user_id', $doctorId)
            ->where('slot_datetime', $slotDt)
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['slot_time' => 'This slot was just taken. Please pick another time.'])->withInput();
        }

        $profile = $doctor->doctorProfile;

        $appointment = Appointment::create([
            'doctor_user_id'  => $doctorId,
            'patient_user_id' => auth()->id(),
            'family_member_id'=> $familyMember?->id,
            'slot_datetime'   => $slotDt,
            'duration_minutes'=> config('medtech.appointment.default_duration', 15),
            'type'            => $request->type,
            'reason'          => $request->reason,
            'status'          => 'booked',
            'fee'             => $profile->consultation_fee,
            'payment_status'  => 'pending',
        ]);

        // Schedule reminders
        $this->reminder->scheduleAppointmentReminders($appointment);

        // WhatsApp confirmation
        try {
            $this->whatsApp->sendAppointmentConfirmation($appointment->load(['doctor.profile', 'patient.profile']));
        } catch (\Exception $e) {
            // non-fatal
        }

        return redirect()
            ->route('patient.appointments.show', $appointment)
            ->with('success', 'Appointment booked! Confirmation sent to your WhatsApp.');
    }

    // ── Show one appointment ─────────────────────────────────────────────────

    public function show(Appointment $appointment)
    {
        if ($appointment->patient_user_id !== auth()->id()) abort(403);
        $appointment->load(['doctor.profile', 'doctor.doctorProfile', 'familyMember']);
        return view('patient.appointments.show', compact('appointment'));
    }

    // ── Cancel ───────────────────────────────────────────────────────────────

    public function cancel(Request $request, Appointment $appointment)
    {
        if ($appointment->patient_user_id !== auth()->id()) abort(403);

        $window = config('medtech.appointment.cancellation_window', 2);
        if ($appointment->slot_datetime->diffInHours(now(), false) > -$window) {
            return back()->withErrors(['cancel' => "Appointments can't be cancelled less than {$window} hours before the slot."]);
        }

        $appointment->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->reason,
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    // ── Reschedule (creates new, cancels old) ────────────────────────────────

    public function reschedule(Request $request, Appointment $appointment)
    {
        if ($appointment->patient_user_id !== auth()->id()) abort(403);

        $request->validate([
            'slot_date' => ['required', 'date', 'after_or_equal:today'],
            'slot_time' => ['required', 'date_format:H:i'],
        ]);

        $slotDt = Carbon::parse($request->slot_date . ' ' . $request->slot_time);

        $conflict = Appointment::where('doctor_user_id', $appointment->doctor_user_id)
            ->where('slot_datetime', $slotDt)
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['slot_time' => 'That slot is already taken.']);
        }

        $new = Appointment::create([
            'doctor_user_id'   => $appointment->doctor_user_id,
            'patient_user_id'  => $appointment->patient_user_id,
            'family_member_id' => $appointment->family_member_id,
            'slot_datetime'    => $slotDt,
            'duration_minutes' => $appointment->duration_minutes,
            'type'             => $appointment->type,
            'reason'           => $appointment->reason,
            'status'           => 'booked',
            'fee'              => $appointment->fee,
            'payment_status'   => 'pending',
            'rescheduled_from' => $appointment->id,
        ]);

        $appointment->update(['status' => 'cancelled', 'cancellation_reason' => 'Rescheduled by patient']);
        $this->reminder->scheduleAppointmentReminders($new);

        return redirect()
            ->route('patient.appointments.show', $new)
            ->with('success', 'Appointment rescheduled successfully.');
    }

    public function create(Request $request)
    {
        return redirect()->route('patient.appointments.book');
    }
}
