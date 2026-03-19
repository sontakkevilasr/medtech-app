<?php

namespace App\Services;

use App\Models\Prescription;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    private string $disk;
    private string $basePath;

    public function __construct()
    {
        $this->disk     = config('medtech.prescription.pdf_disk', 'local');
        $this->basePath = config('medtech.prescription.pdf_path', 'prescriptions/pdfs');
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Generate a PDF for a prescription and save it to storage.
     * Returns the storage path on success.
     */
    public function generatePrescriptionPdf(Prescription $prescription): string
    {
        $prescription->load(['doctor.profile', 'doctor.doctorProfile', 'patient.profile', 'medicines', 'familyMember']);

        $pdf = Pdf::loadView('doctor.prescriptions.pdf', [
            'prescription' => $prescription,
            'doctor'       => $prescription->doctor,
            'patient'      => $prescription->patient,
            'medicines'    => $prescription->medicines,
            'familyMember' => $prescription->familyMember,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'dpi'                  => 150,
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
        ]);

        $filename = $this->pdfFilename($prescription);
        $path     = "{$this->basePath}/{$filename}";

        Storage::disk($this->disk)->put($path, $pdf->output());

        // Update prescription record with path
        $prescription->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Get a PDF for download — generate if it doesn't exist yet.
     */
    public function getOrGenerate(Prescription $prescription): string
    {
        if ($prescription->pdf_path && Storage::disk($this->disk)->exists($prescription->pdf_path)) {
            return $prescription->pdf_path;
        }

        return $this->generatePrescriptionPdf($prescription);
    }

    /**
     * Stream a prescription PDF directly to the browser (inline preview).
     */
    public function streamPrescription(Prescription $prescription): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = $this->getOrGenerate($prescription);

        return Storage::disk($this->disk)->response(
            $path,
            $this->pdfFilename($prescription),
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Force-download a prescription PDF.
     */
    public function downloadPrescription(Prescription $prescription): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = $this->getOrGenerate($prescription);

        return Storage::disk($this->disk)->download(
            $path,
            $this->pdfFilename($prescription),
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Delete the stored PDF for a prescription (e.g. when prescription is cancelled).
     */
    public function deletePrescriptionPdf(Prescription $prescription): void
    {
        if ($prescription->pdf_path && Storage::disk($this->disk)->exists($prescription->pdf_path)) {
            Storage::disk($this->disk)->delete($prescription->pdf_path);
        }
        $prescription->update(['pdf_path' => null]);
    }

    /**
     * Regenerate PDF (used when prescription is edited after issuance).
     */
    public function regenerate(Prescription $prescription): string
    {
        $this->deletePrescriptionPdf($prescription);
        return $this->generatePrescriptionPdf($prescription);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    private function pdfFilename(Prescription $prescription): string
    {
        $safe = str_replace('-', '', $prescription->prescription_number);
        return "{$safe}_{$prescription->patient_user_id}.pdf";
    }

    /**
     * Stream PDF inline in browser (for preview).
     */
    public function stream(Prescription $prescription): \Symfony\Component\HttpFoundation\Response
    {
        $path = $this->getOrGenerate($prescription);
        return Storage::disk($this->disk)->response(
            $path, $this->pdfFilename($prescription),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.$this->pdfFilename($prescription).'"']
        );
    }

    /**
     * Force-download the PDF.
     */
    public function download(Prescription $prescription): \Symfony\Component\HttpFoundation\Response
    {
        $path = $this->getOrGenerate($prescription);
        return Storage::disk($this->disk)->download($path, $this->pdfFilename($prescription));
    }

}
