<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientHistoryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientHistoryDocumentController extends Controller
{
    private const ALLOWED_MIME = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    ];

    private const MAX_MB = 10;

    public function index(Request $request)
    {
        $patient  = auth()->user();
        $memberId = $request->integer('member') ?: null;

        $docs = PatientHistoryDocument::where('patient_user_id', $patient->id)
            ->when($memberId,
                fn($q) => $q->where('family_member_id', $memberId),
                fn($q) => $q->whereNull('family_member_id')
            )
            ->orderByDesc('document_date')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($docs->map(fn($d) => [
            'id'            => $d->id,
            'title'         => $d->title,
            'document_type' => $d->document_type,
            'notes'         => $d->notes,
            'file_name'     => $d->file_name,
            'file_size'     => $d->file_size_human,
            'mime_type'     => $d->mime_type,
            'document_date' => $d->document_date?->format('d M Y'),
            'created_at'    => $d->created_at->format('d M Y'),
            'download_url'  => $d->download_url,
            'view_url'      => $d->view_url,
            'is_image'      => str_starts_with($d->mime_type ?? '', 'image/'),
        ])->values());
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'          => ['required', 'file', 'max:' . (self::MAX_MB * 1024),
                                'mimes:pdf,jpg,jpeg,png,webp,gif'],
            'title'         => ['required', 'string', 'max:200'],
            'document_type' => ['required', 'in:prescription,lab_report,discharge_summary,scan_xray,vaccination,insurance,other'],
            'document_date' => ['nullable', 'date', 'before_or_equal:today'],
            'notes'         => ['nullable', 'string', 'max:500'],
            'member_id'     => ['nullable', 'integer'],
        ]);

        $patient  = auth()->user();
        $memberId = $request->integer('member_id') ?: null;

        // Validate family member belongs to this patient
        if ($memberId) {
            $patient->familyMembers()->findOrFail($memberId);
        }

        $file     = $request->file('file');
        $dir      = 'patient-docs/' . $patient->id;
        $stored   = $file->storeAs($dir, Str::uuid() . '.' . $file->getClientOriginalExtension(), 'local');

        $doc = PatientHistoryDocument::create([
            'patient_user_id' => $patient->id,
            'family_member_id'=> $memberId,
            'title'           => $request->title,
            'document_type'   => $request->document_type,
            'notes'           => $request->notes,
            'file_path'       => $stored,
            'file_name'       => $file->getClientOriginalName(),
            'mime_type'       => $file->getMimeType(),
            'file_size'       => $file->getSize(),
            'document_date'   => $request->document_date,
        ]);

        return response()->json([
            'success'       => true,
            'id'            => $doc->id,
            'title'         => $doc->title,
            'document_type' => $doc->document_type,
            'notes'         => $doc->notes,
            'file_name'     => $doc->file_name,
            'file_size'     => $doc->file_size_human,
            'mime_type'     => $doc->mime_type,
            'document_date' => $doc->document_date?->format('d M Y'),
            'created_at'    => $doc->created_at->format('d M Y'),
            'download_url'  => $doc->download_url,
            'view_url'      => $doc->view_url,
            'is_image'      => str_starts_with($doc->mime_type ?? '', 'image/'),
        ], 201);
    }

    public function download(PatientHistoryDocument $document)
    {
        if ($document->patient_user_id !== auth()->id()) abort(403);

        if (!Storage::disk('local')->exists($document->file_path)) abort(404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->file_name
        );
    }

    public function view(PatientHistoryDocument $document)
    {
        if ($document->patient_user_id !== auth()->id()) abort(403);

        if (!Storage::disk('local')->exists($document->file_path)) abort(404);

        return Storage::disk('local')->response(
            $document->file_path,
            $document->file_name
        );
    }

    public function destroy(PatientHistoryDocument $document)
    {
        if ($document->patient_user_id !== auth()->id()) abort(403);

        $document->deleteFile();
        $document->delete();

        return response()->json(['success' => true]);
    }
}
