<?php

namespace App\Rules\Adult;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class AdultGuardian implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $requester = auth()->user();

        if ($requester && $requester->isAdult()) {
            $fail('You are already an adult. Guardian is not required.');

            return;
        }

        $guardian = User::query()->where('email', $value)->first();

        if (! $guardian) {
            $fail('Guardian with this email does not exist.');

            return;
        }

        if (! $guardian->date_of_birth || $guardian->date_of_birth->age < 18) {
            $fail('Guardian must be at least 18 years old.');
        }
    }
}
