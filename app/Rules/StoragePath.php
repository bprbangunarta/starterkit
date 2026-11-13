<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** A place inside a storage: empty for the top, otherwise folders joined by "/" with no climbing out, no empty part, and no control character. */
class StoragePath implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || strlen($value) > 1024) {
            $fail('Lokasi tidak valid.');

            return;
        }

        if ($value === '') {
            return;
        }

        $parts = explode('/', $value);
        $climbs = in_array('.', $parts, true) || in_array('..', $parts, true) || in_array('', $parts, true);

        if ($climbs || preg_match('/[\x00-\x1F\x7F\\\\]/', $value) === 1) {
            $fail('Lokasi tidak valid.');
        }
    }
}
