<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use App\Services\NotificationService;


class DoctorVerificationController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('admin.verification.pending');
    }

    public function pending(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $query = User::doctors()
            ->with(['profile', 'doctorProfile'])
            ->where('is_active', true);

        match ($tab) {
            'pending'  => $query->whereHas('doctorProfile', fn($q) => $q->where('is_verified', false)),
            'verified' => $query->whereHas('doctorProfile', fn($q) => $q->where('is_verified', true)),
            default    => $query->whereHas('doctorProfile'),
        };

        $doctors = $query->latest()->paginate(20)->withQueryString();

        $counts = [
            'pending'  => User::doctors()->active()->whereHas('doctorProfile', fn($q) => $q->where('is_verified', false))->count(),
            'verified' => User::doctors()->active()->whereHas('doctorProfile', fn($q) => $q->where('is_verified', true))->count(),
        ];

        return view('admin.verification.index', compact('doctors', 'tab', 'counts'));
    }

    public function show(int $doctorId)
    {
        $doctor = User::where('id', $doctorId)->where('role', 'doctor')
            ->with(['profile', 'doctorProfile'])
            ->firstOrFail();

        return view('admin.verification.show', compact('doctor'));
    }

    public function approve(Request $request, int $doctorId)
    {
        $doctor = User::where('id', $doctorId)->where('role', 'doctor')->firstOrFail();

        $doctor->doctorProfile()->update([
            'is_verified'     => true,
            'verified_at'     => now(),
            'rejection_reason'=> null,
        ]);

        NotificationService::doctorVerified($doctor);


        // Notify doctor (WhatsApp / future)
        try {
            app(\App\Services\WhatsAppService::class)
                ->sendVerificationApproved($doctor->load('profile'));
        } catch (\Exception) {}

        return redirect()
            ->route('admin.verification.pending')
            ->with('success', "Dr. {$doctor->profile?->full_name} has been verified and can now accept appointments.");
    }

    public function reject(Request $request, int $doctorId)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $doctor = User::where('id', $doctorId)->where('role', 'doctor')->firstOrFail();

        $doctor->doctorProfile()->update([
            'is_verified'      => false,
            'rejection_reason' => $request->reason,
        ]);
        NotificationService::doctorRejected($doctor, $request->reason);
        
        // Suspend until they fix it
        $doctor->update(['is_active' => false]);

        return redirect()
            ->route('admin.verification.pending')
            ->with('warning', "Dr. {$doctor->profile?->full_name}'s verification was rejected. Account suspended.");
    }
}
