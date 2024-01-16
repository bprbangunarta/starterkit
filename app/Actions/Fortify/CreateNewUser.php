<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Validation\ValidationException;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        try {
            Validator::make($input, [
                'name'      => ['required', 'string', 'max:255'],
                'username'  => ['required', 'string', 'max:255', 'unique:users'],
                'phone'     => ['required', 'min:11', 'max:12', 'unique:users'],
                'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password'  => $this->passwordRules(),
                'terms'     => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
            ])->validate();

            return User::create([
                'name'      => $input['name'],
                'username'  => $input['username'],
                'phone'     => '62' . $input['phone'],
                'email'     => $input['email'],
                'password'  => Hash::make($input['password']),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];

            if ($errorCode == 1062) { // Duplicate entry error code
                throw ValidationException::withMessages(['phone' => 'The phone has already been taken.']);
            }

            throw $e; // Re-throw the exception if it's not a duplicate entry error
        }
    }
}
