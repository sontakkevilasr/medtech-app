<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    // ── Shared query builder ──────────────────────────────────────────────────

    private function userQuery(string $role = null)
    {
        $q = User::with(['profile', 'doctorProfile'])
            ->when($role, fn($q) => $q->where('role', $role))
            ->when(!$role, fn($q) => $q->whereIn('role', ['doctor','patient']));
        return $q;
    }

    // ── All Users ─────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query  = $this->userQuery();
        $this->applyFilters($query, $request);
        $users  = $query->latest()->paginate(20)->withQueryString();
        $role   = null;
        return view('admin.users.index', compact('users', 'role') + $this->filterBag($request));
    }

    public function doctors(Request $request)
    {
        $query = $this->userQuery('doctor');
        $this->applyFilters($query, $request);
        $users = $query->latest()->paginate(20)->withQueryString();
        $role  = 'doctor';
        return view('admin.users.index', compact('users', 'role') + $this->filterBag($request));
    }

    public function patients(Request $request)
    {
        $query = $this->userQuery('patient');
        $this->applyFilters($query, $request);
        $users = $query->latest()->paginate(20)->withQueryString();
        $role  = 'patient';
        return view('admin.users.index', compact('users', 'role') + $this->filterBag($request));
    }

    // ── Show one user ─────────────────────────────────────────────────────────

    public function show(User $user)
    {
        $user->load(['profile', 'doctorProfile', 'familyMembers']);

        $stats = [];
        if ($user->isDoctor()) {
            $stats = [
                'total_appointments' => Appointment::where('doctor_user_id', $user->id)->count(),
                'appointments_month' => Appointment::where('doctor_user_id', $user->id)
                    ->whereMonth('slot_datetime', now()->month)->count(),
                'total_patients'     => Appointment::where('doctor_user_id', $user->id)
                    ->distinct('patient_user_id')->count('patient_user_id'),
                'total_prescriptions'=> Prescription::where('doctor_user_id', $user->id)->count(),
            ];
        } elseif ($user->isPatient()) {
            $stats = [
                'total_appointments' => Appointment::where('patient_user_id', $user->id)->count(),
                'total_prescriptions'=> Prescription::where('patient_user_id', $user->id)->count(),
                'family_members'     => $user->familyMembers->count(),
                'access_grants'      => \App\Models\DoctorAccessRequest::where('patient_user_id', $user->id)
                    ->active()->count(),
            ];
        }

        $recentAppointments = Appointment::where(
            $user->isDoctor() ? 'doctor_user_id' : 'patient_user_id', $user->id
        )->with(['doctor.profile','patient.profile'])->latest('slot_datetime')->limit(5)->get();

        return view('admin.users.show', compact('user', 'stats', 'recentAppointments'));
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function activate(Request $request, User $user)
    {
        $user->update(['is_active' => true]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => 'active']);
        }
        return back()->with('success', "User {$user->profile?->full_name} has been activated.");
    }

    public function suspend(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Cannot suspend an admin user.']);
        }
        $user->update(['is_active' => false]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => 'suspended']);
        }
        return back()->with('success', "User {$user->profile?->full_name} has been suspended.");
    }

    public function grantPremium(Request $request, User $user)
    {
        if (! $user->isDoctor()) {
            return back()->withErrors(['user' => 'Premium can only be granted to doctors.']);
        }

        $until = $request->input('until', now()->addYear()->format('Y-m-d'));

        $user->doctorProfile()->update([
            'is_premium'         => true,
            'premium_expires_at' => $until,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', "Premium access granted to Dr. {$user->profile?->full_name} until " . \Carbon\Carbon::parse($until)->format('d M Y') . '.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Cannot delete an admin user.']);
        }

        $name = $user->profile?->full_name ?? 'User';
        $user->delete(); // soft delete

        return redirect()
            ->route('admin.users.index')
            ->with('success', "{$name} has been removed from the platform.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function applyFilters($query, Request $request): void
    {
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('profile', fn($qq) => $qq->where('full_name', 'like', "%{$search}%"))
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            match ($status) {
                'active'    => $query->where('is_active', true),
                'suspended' => $query->where('is_active', false),
                default     => null,
            };
        }

        if ($verified = $request->get('verified')) {
            $query->whereHas('doctorProfile', fn($q) =>
                $q->where('is_verified', $verified === 'yes')
            );
        }

        if ($premium = $request->get('premium')) {
            $query->whereHas('doctorProfile', fn($q) =>
                $q->where('is_premium', $premium === 'yes')
            );
        }
    }

    private function filterBag(Request $request): array
    {
        return [
            'search'   => $request->get('q'),
            'status'   => $request->get('status'),
            'verified' => $request->get('verified'),
            'premium'  => $request->get('premium'),
        ];
    }
}
