<?php

namespace App\Enums;

/** Base roles needed in every environment. The values are the names stored in the `roles` table. */
enum RoleName: string
{
    case SuperAdmin = 'Super Admin';
    case Guest = 'Guest';
}
