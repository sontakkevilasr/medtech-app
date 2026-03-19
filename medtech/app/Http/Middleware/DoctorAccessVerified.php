<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\DoctorAccessRequest;

class DoctorAccessVerified
{
    /**
     * Used on routes where a doctor views a specific patient's medical records.
     * Checks that the doctor has an active approved access grant for that patient.
     *
     * Expects route parameter: {patient}  (patient user ID)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $doctor    = $request->user();
        $patientId = $request->route('patient');   // route model binding ID

        if (! $patientId) {
            return $next($request);  // not a patient-specific route — skip
        }

        // Check if patient has granted full access globally
        $permission = \App\Models\PatientAccessPermission::where('patient_user_id', $patientId)
            ->whereNull('family_member_id')
            ->first();

        if ($permission && $permission->isFullAccess()) {
            return $next($request);  // full access — no OTP needed
        }

        // Otherwise check for an active access request from this doctor
        $activeAccess = DoctorAccessRequest::where('doctor_user_id', $doctor->id)
            ->where('patient_user_id', $patientId)
            ->active()
            ->exists();

        if ($activeAccess) {
            return $next($request);
        }

        return redirect()->route('doctor.patients.request-access', ['patient' => $patientId])
            ->with('info', 'You need patient approval to view their medical records.');
    }
}
