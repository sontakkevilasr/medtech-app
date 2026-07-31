<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixDoctorNames extends Command
{
    protected $signature = 'users:fix-doctor-names';

    protected $description = 'Strip a redundant "Dr."/"Doctor" honorific from doctor profile names so it does not render twice (e.g. "Dr. Dr. Aparna Arya Tyagi")';

    public function handle(): int
    {
        $doctors = User::doctors()->with('profile')->get();
        $fixed   = 0;

        foreach ($doctors as $doctor) {
            $profile = $doctor->profile;
            if (!$profile || !$profile->full_name) {
                continue;
            }

            $original = $profile->getRawOriginal('full_name');
            $cleaned  = preg_replace('/^(dr\.?|doctor)\s+/i', '', trim($original));

            if ($cleaned !== $original) {
                $profile->full_name = $cleaned; // mutator re-applies the same stripping
                $profile->save();
                $this->line("Fixed #{$doctor->id}: \"{$original}\" -> \"{$cleaned}\"");
                $fixed++;
            }
        }

        $this->info($fixed > 0
            ? "Done. Cleaned {$fixed} doctor name(s)."
            : 'Done. No doctor names needed cleaning.');

        return self::SUCCESS;
    }
}
