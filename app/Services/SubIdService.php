<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyMember;
use Illuminate\Support\Str;

class SubIdService
{
    private string $prefix;
    private int    $padding;
    private string $suffixChars;

    public function __construct()
    {
        $this->prefix      = config('medtech.sub_id.prefix', 'MED');
        $this->padding     = config('medtech.sub_id.padding', 5);
        $this->suffixChars = config('medtech.sub_id.suffix_chars', 'ABCDEFGHJKLMNPQRSTUVWXYZ');
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Generate a unique sub-ID for a new family member.
     * Format: MED-00042-C
     */
    public function generate(User $primaryUser): string
    {
        $paddedUserId = str_pad($primaryUser->id, $this->padding, '0', STR_PAD_LEFT);
        $suffix       = $this->nextSuffix($primaryUser);

        return "{$this->prefix}-{$paddedUserId}-{$suffix}";
    }

    /**
     * Delink a family member sub-ID from its primary account.
     * Optionally link it to a new mobile number.
     */
    public function delink(FamilyMember $member, ?string $newMobile = null, ?string $countryCode = '+91'): FamilyMember
    {
        $member->update([
            'is_delinked'         => true,
            'linked_mobile'       => $newMobile,
            'linked_country_code' => $countryCode,
            'linked_user_id'      => $newMobile ? $this->findUserByMobile($newMobile, $countryCode)?->id : null,
            'delinked_at'         => now(),
        ]);

        return $member->fresh();
    }

    /**
     * Relink a delinked sub-ID back to a primary user account.
     */
    public function relink(FamilyMember $member, User $newPrimaryUser): FamilyMember
    {
        $member->update([
            'primary_user_id'     => $newPrimaryUser->id,
            'is_delinked'         => false,
            'linked_mobile'       => null,
            'linked_country_code' => null,
            'linked_user_id'      => null,
            'delinked_at'         => null,
        ]);

        return $member->fresh();
    }

    /**
     * Find a family member by their sub-ID string.
     */
    public function findBySubId(string $subId): ?FamilyMember
    {
        return FamilyMember::where('sub_id', strtoupper(trim($subId)))->first();
    }

    /**
     * Validate that a sub-ID string matches the expected format.
     */
    public function isValidFormat(string $subId): bool
    {
        $pattern = '/^' . $this->prefix . '-\d{' . $this->padding . '}-[A-Z]$/';
        return (bool) preg_match($pattern, strtoupper($subId));
    }

    /**
     * Return all sub-IDs for a given primary user, including delinked ones.
     */
    public function allForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return FamilyMember::where('primary_user_id', $user->id)
            ->withTrashed()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Transfer all active family members from one user to another.
     * Used when merging accounts.
     */
    public function transferAll(User $fromUser, User $toUser): int
    {
        return FamilyMember::where('primary_user_id', $fromUser->id)
            ->where('is_delinked', false)
            ->update(['primary_user_id' => $toUser->id]);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Determine the next available suffix letter for this user.
     * Self = A, first added member = B, next = C, ...
     * Skips already-used suffixes (including soft-deleted members).
     */
    private function nextSuffix(User $primaryUser): string
    {
        $paddedUserId = str_pad($primaryUser->id, $this->padding, '0', STR_PAD_LEFT);
        $prefix       = "{$this->prefix}-{$paddedUserId}-";

        // Get all used suffixes for this user (including deleted)
        $usedSuffixes = FamilyMember::withTrashed()
            ->where('primary_user_id', $primaryUser->id)
            ->pluck('sub_id')
            ->map(fn($id) => substr($id, -1))   // get the last character
            ->toArray();

        // Walk through suffix chars and find the first unused
        foreach (str_split($this->suffixChars) as $char) {
            if (! in_array($char, $usedSuffixes)) {
                return $char;
            }
        }

        // Fallback: use random 2-char suffix (shouldn't reach here normally)
        return Str::upper(Str::random(2));
    }

    private function findUserByMobile(string $mobile, string $countryCode): ?User
    {
        return User::where('mobile_number', $mobile)
            ->where('country_code', $countryCode)
            ->first();
    }
}
