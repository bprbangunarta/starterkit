<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** The name of one folder or file: no "/" or "\", no control character, and not "." or "..". */
class StorageItemName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '' || in_array(trim($value), ['.', '..'], true)) {
            $fail('Nama tidak valid.');

            return;
        }

        if (mb_strlen($value) > 200 || preg_match('/[\x00-\x1F\x7F\/\\\\]/', $value) === 1) {
            $fail('Nama tidak boleh memuat garis miring atau karakter kontrol, dan paling banyak 200 karakter.');
        }
    }
}
