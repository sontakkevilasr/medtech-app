<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExportService
{
    // ─── Public Exports ──────────────────────────────────────────────────────

    /**
     * Export all patients for a doctor with last visit summary.
     */
    public function exportPatients(User $doctor): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = $this->buildPatientRows($doctor);

        return Excel::download(
            new GenericExport(
                data:     $rows,
                headings: [
                    'Patient Name', 'Mobile', 'Age', 'Gender', 'Blood Group',
                    'City', 'Last Visit', 'Diagnosis', 'Next Follow-up', 'Total Visits',
                ],
                title:    'Patient List',
                filename: 'patients'
            ),
            "patients_{$doctor->id}_" . now()->format('Ymd') . ".xlsx"
        );
    }

    /**
     * Export all appointments for a doctor (date range optional).
     */
    public function exportAppointments(
        User $doctor,
        ?string $from = null,
        ?string $to = null
    ): \Symfony\Component\HttpFoundation\BinaryFileResponse {
        $rows = $this->buildAppointmentRows($doctor, $from, $to);

        return Excel::download(
            new GenericExport(
                data:     $rows,
                headings: [
                    'Apt. No', 'Patient Name', 'Mobile', 'Date & Time',
                    'Type', 'Status', 'Fee (₹)', 'Payment Status', 'Reason',
                ],
                title:    'Appointments',
                filename: 'appointments'
            ),
            "appointments_{$doctor->id}_" . now()->format('Ymd') . ".xlsx"
        );
    }

    /**
     * Export all prescriptions for a doctor.
     */
    public function exportPrescriptions(User $doctor): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = $this->buildPrescriptionRows($doctor);

        return Excel::download(
            new GenericExport(
                data:     $rows,
                headings: [
                    'Rx No', 'Patient Name', 'Date', 'Diagnosis',
                    'No. of Medicines', 'WhatsApp Sent', 'Follow-up Date',
                ],
                title:    'Prescriptions',
                filename: 'prescriptions'
            ),
            "prescriptions_{$doctor->id}_" . now()->format('Ymd') . ".xlsx"
        );
    }

    // ─── Row Builders ────────────────────────────────────────────────────────

    private function buildPatientRows(User $doctor): Collection
    {
        return $doctor->doctorMedicalRecords()
            ->with(['patient.profile', 'patient.medicalRecords'])
            ->get()
            ->groupBy('patient_user_id')
            ->map(function ($records) {
                $latest  = $records->sortByDesc('visit_date')->first();
                $patient = $latest->patient;
                $profile = $patient->profile;

                return [
                    $profile->full_name                                     ?? '—',
                    $patient->country_code . $patient->mobile_number,
                    $profile->age                                           ?? '—',
                    ucfirst($profile->gender                                ?? '—'),
                    $profile->blood_group                                   ?? '—',
                    $profile->city                                          ?? '—',
                    $latest->visit_date?->format('d M Y')                  ?? '—',
                    $latest->diagnosis                                      ?? '—',
                    $latest->follow_up_date?->format('d M Y')              ?? '—',
                    $records->count(),
                ];
            })
            ->values();
    }

    private function buildAppointmentRows(User $doctor, ?string $from, ?string $to): Collection
    {
        $query = $doctor->doctorAppointments()
            ->with(['patient.profile'])
            ->orderByDesc('slot_datetime');

        if ($from) $query->where('slot_datetime', '>=', $from);
        if ($to)   $query->where('slot_datetime', '<=', $to . ' 23:59:59');

        return $query->get()->map(fn($apt) => [
            $apt->appointment_number,
            $apt->patient->profile->full_name                      ?? '—',
            $apt->patient->country_code . $apt->patient->mobile_number,
            $apt->slot_datetime->format('d M Y h:i A'),
            ucfirst($apt->type),
            ucfirst($apt->status),
            number_format($apt->fee, 2),
            ucfirst($apt->payment_status),
            $apt->reason                                           ?? '—',
        ]);
    }


    // ─── Admin / Platform-wide Exports ───────────────────────────────────────

    public function exportAllUsers(string $role = null): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $users = User::whereIn('role', $role ? [$role] : ['doctor', 'patient'])
            ->with(['profile', 'doctorProfile'])
            ->latest()
            ->get()
            ->map(fn($u) => [
                $u->id,
                $u->profile?->full_name ?? '—',
                ucfirst($u->role),
                $u->country_code . $u->mobile_number,
                $u->profile?->email ?? '—',
                $u->profile?->city ?? $u->doctorProfile?->clinic_city ?? '—',
                $u->profile?->state ?? '—',
                $u->is_active ? 'Active' : 'Suspended',
                $u->is_verified ? 'Yes' : 'No',
                $u->doctorProfile?->specialization ?? '—',
                $u->doctorProfile?->is_verified ? 'Yes' : 'No',
                $u->doctorProfile?->is_premium ? 'Yes' : 'No',
                $u->created_at->format('d M Y'),
            ]);

        $label = $role ? ucfirst($role).'s' : 'All Users';
        return Excel::download(
            new GenericExport(
                data:     collect($users),
                headings: ['ID','Name','Role','Mobile','Email','City','State','Status','Mobile Verified','Specialization','Doc Verified','Premium','Joined'],
                title:    $label,
            ),
            strtolower(str_replace(' ','_',$label)) . '_' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function exportAllAppointments(?string $from = null, ?string $to = null): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $query = \App\Models\Appointment::with(['doctor.profile','patient.profile'])
            ->orderByDesc('slot_datetime');
        if ($from) $query->whereDate('slot_datetime', '>=', $from);
        if ($to)   $query->whereDate('slot_datetime', '<=', $to);

        $rows = $query->get()->map(fn($apt) => [
            $apt->appointment_number,
            $apt->doctor?->profile?->full_name ?? '—',
            $apt->patient?->profile?->full_name ?? '—',
            $apt->patient?->country_code . $apt->patient?->mobile_number,
            $apt->slot_datetime->format('d M Y h:i A'),
            ucfirst(str_replace('_',' ',$apt->type)),
            ucfirst($apt->status),
            $apt->fee ? '₹'.number_format($apt->fee,2) : '—',
            ucfirst($apt->payment_status ?? 'pending'),
            $apt->reason ?? '—',
        ]);

        return Excel::download(
            new GenericExport(
                data:     $rows,
                headings: ['Apt No','Doctor','Patient','Mobile','Date & Time','Type','Status','Fee','Payment','Reason'],
                title:    'Appointments',
            ),
            'appointments_' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function exportDoctorVerification(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = User::doctors()
            ->with(['profile','doctorProfile'])
            ->get()
            ->map(fn($u) => [
                $u->id,
                $u->profile?->full_name ?? '—',
                $u->country_code . $u->mobile_number,
                $u->doctorProfile?->specialization ?? '—',
                $u->doctorProfile?->qualification ?? '—',
                $u->doctorProfile?->registration_number ?? '—',
                $u->doctorProfile?->registration_council ?? '—',
                $u->doctorProfile?->is_verified ? 'Verified' : 'Pending',
                $u->is_active ? 'Active' : 'Suspended',
                $u->doctorProfile?->is_premium ? 'Premium' : 'Free',
                $u->created_at->format('d M Y'),
            ]);

        return Excel::download(
            new GenericExport(
                data:     $rows,
                headings: ['ID','Name','Mobile','Specialty','Qualification','Reg No','Council','Verified','Status','Plan','Joined'],
                title:    'Doctor Verification',
            ),
            'doctor_verification_' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function exportPlatformStats(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        // Monthly stats for last 12 months
        $rows = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $rows->push([
                $month->format('M Y'),
                User::doctors()->whereYear('created_at',$month->year)->whereMonth('created_at',$month->month)->count(),
                User::patients()->whereYear('created_at',$month->year)->whereMonth('created_at',$month->month)->count(),
                \App\Models\Appointment::whereYear('slot_datetime',$month->year)->whereMonth('slot_datetime',$month->month)->count(),
                \App\Models\Appointment::whereYear('slot_datetime',$month->year)->whereMonth('slot_datetime',$month->month)->where('status','completed')->count(),
                \App\Models\Appointment::whereYear('slot_datetime',$month->year)->whereMonth('slot_datetime',$month->month)->where('status','cancelled')->count(),
                \App\Models\Prescription::whereYear('created_at',$month->year)->whereMonth('created_at',$month->month)->count(),
                \App\Models\Payment::paid()->whereYear('paid_at',$month->year)->whereMonth('paid_at',$month->month)->sum('amount'),
            ]);
        }

        return Excel::download(
            new GenericExport(
                data:     $rows,
                headings: ['Month','New Doctors','New Patients','Total Apts','Completed','Cancelled','Prescriptions','Revenue (₹)'],
                title:    'Platform Stats',
            ),
            'platform_stats_' . now()->format('Ymd') . '.xlsx'
        );
    }

        private function buildPrescriptionRows(User $doctor): Collection
    {
        return $doctor->doctorPrescriptions()
            ->with(['patient.profile', 'medicines'])
            ->orderByDesc('prescribed_date')
            ->get()
            ->map(fn($rx) => [
                $rx->prescription_number,
                $rx->patient->profile->full_name                   ?? '—',
                $rx->prescribed_date->format('d M Y'),
                $rx->diagnosis_summary                             ?? '—',
                $rx->medicines->count(),
                $rx->is_sent_whatsapp ? 'Yes' : 'No',
                $rx->follow_up_date?->format('d M Y')              ?? '—',
            ]);
    }
}

// ─── Reusable Excel Export Class ─────────────────────────────────────────────

class GenericExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function __construct(
        private Collection $data,
        private array      $headings,
        private string     $title    = 'Sheet',
        private string     $filename = 'export',
    ) {}

    public function collection(): Collection { return $this->data; }

    public function headings(): array { return $this->headings; }

    public function title(): string { return $this->title; }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Bold + coloured header row
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function columnWidths(): array
    {
        // Auto-set width based on heading length
        $widths = [];
        $cols   = range('A', 'Z');
        foreach ($this->headings as $i => $heading) {
            $widths[$cols[$i]] = max(15, strlen($heading) + 4);
        }
        return $widths;
    }
}
// The file already has GenericExport at the bottom — appending admin exports would break it.
// We'll update via str_replace instead.
