<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\FamilyMember;
use App\Services\SubIdService;
use Illuminate\Http\Request;

class FamilyMemberController extends Controller
{
    public function __construct(private SubIdService $subIdService) {}

    // ── List ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $patient = auth()->user();
        $members = FamilyMember::where('primary_user_id', $patient->id)
            ->withTrashed()
            ->orderBy('created_at')
            ->get();

        // Self sub-ID (generated once, stored on the member with relation=self)
        $self = $members->firstWhere('relation', 'self');

        // Active family members
        $active   = $members->where('relation', '!=', 'self')->where('is_delinked', false)->where('deleted_at', null)->values();
        $delinked = $members->where('is_delinked', true)->values();
        $deleted  = $members->filter(fn($m) => $m->deleted_at !== null)->values();

        return view('patient.family.index', compact(
            'patient', 'self', 'active', 'delinked', 'deleted'
        ));
    }

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create()
    {
        return view('patient.family.create', ['member' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'   => ['required', 'string', 'max:100'],
            'relation'    => ['required', 'in:spouse,child,parent,sibling,grandparent,other'],
            'dob'         => ['nullable', 'date', 'before:today'],
            'gender'      => ['nullable', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
        ]);

        $patient = auth()->user();
        $subId   = $this->subIdService->generate($patient);

        FamilyMember::create([
            'primary_user_id' => $patient->id,
            'sub_id'          => $subId,
            'full_name'       => $request->full_name,
            'relation'        => $request->relation,
            'dob'             => $request->dob,
            'gender'          => $request->gender,
            'blood_group'     => $request->blood_group,
        ]);

        return redirect()
            ->route('patient.family.index')
            ->with('success', "{$request->full_name} added! Their Sub-ID is {$subId}.");
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function show(int $member)
    {
        $member = FamilyMember::withTrashed()
            ->where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->firstOrFail();

        // Recent appointments & prescriptions for this member
        $appointments  = $member->appointments()->with('doctor.profile')->latest('slot_datetime')->limit(5)->get();
        $prescriptions = $member->prescriptions()->with('doctor.profile')->latest()->limit(5)->get();

        return view('patient.family.show', compact('member', 'appointments', 'prescriptions'));
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(int $member)
    {
        $member = FamilyMember::where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->firstOrFail();

        return view('patient.family.edit', compact('member'));
    }

    public function update(Request $request, int $member)
    {
        $member = FamilyMember::where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'full_name'   => ['required', 'string', 'max:100'],
            'dob'         => ['nullable', 'date', 'before:today'],
            'gender'      => ['nullable', 'in:male,female,other'],
            'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
        ]);

        $member->update($request->only('full_name', 'dob', 'gender', 'blood_group'));

        return redirect()
            ->route('patient.family.show', $member->id)
            ->with('success', 'Profile updated.');
    }

    // ── Soft-delete (remove from household, keep Sub-ID record) ──────────────

    public function destroy(int $member)
    {
        $member = FamilyMember::where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->firstOrFail();

        if ($member->relation === 'self') {
            return back()->withErrors(['member' => 'Cannot remove your own self profile.']);
        }

        $member->delete();

        return redirect()
            ->route('patient.family.index')
            ->with('success', "{$member->full_name} removed. Their Sub-ID record is retained for history.");
    }

    // ── Delink Sub-ID to another mobile ──────────────────────────────────────
    // (Gives the family member their own independent "account")

    public function delink(Request $request, int $member)
    {
        $member = FamilyMember::where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->where('is_delinked', false)
            ->firstOrFail();

        $request->validate([
            'linked_mobile'       => ['required', 'regex:/^[6-9]\d{9}$/'],
            'linked_country_code' => ['nullable', 'string', 'max:5'],
        ]);

        $this->subIdService->delink(
            member:      $member,
            newMobile:   $request->linked_mobile,
            countryCode: $request->linked_country_code ?? '+91',
        );

        return back()->with('success',
            "{$member->full_name}'s Sub-ID has been delinked. It is now associated with {$request->linked_mobile}."
        );
    }

    // ── Relink a delinked Sub-ID back ─────────────────────────────────────────

    public function relink(int $member)
    {
        $member = FamilyMember::withTrashed()
            ->where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->where('is_delinked', true)
            ->firstOrFail();

        $this->subIdService->relink($member, auth()->user());

        // Restore soft-delete if applicable
        if ($member->trashed()) {
            $member->restore();
        }

        return back()->with('success',
            "{$member->full_name}'s Sub-ID has been re-linked to your account."
        );
    }

    // ── Regenerate Sub-ID (emergency – old one is compromised) ───────────────

    public function regenerateSubId(int $member)
    {
        $member = FamilyMember::where('id', $member)
            ->where('primary_user_id', auth()->id())
            ->firstOrFail();

        $oldId = $member->sub_id;
        $newId = $this->subIdService->generate(auth()->user());

        $member->update(['sub_id' => $newId]);

        return back()->with('success',
            "Sub-ID regenerated. Old ID ({$oldId}) is now invalid. New ID: {$newId}"
        );
    }
}
