<?php

namespace App\Actions\Offices;

use App\Models\Office;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteOffice
{
    /** An office that still has users, deleted users included, stays: the database refuses it too. */
    public function handle(Office $office): void
    {
        DB::transaction(function () use ($office): void {
            // The row is locked while it is checked, so an office cannot be deleted at the moment it is given to a user.
            Office::query()->whereKey($office->id)->lockForUpdate()->first();

            if ($office->users()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['office' => 'Kantor masih dipakai pengguna. Pindahkan penggunanya lebih dulu.']);
            }

            $office->delete();
        });
    }
}
