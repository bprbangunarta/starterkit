<?php

namespace App\Enums;

/** Which accounts the user list shows: the ones that can sign in, the deleted ones, or both. */
enum UserListStatus: string
{
    case Active = 'active';
    case Deleted = 'deleted';
    case All = 'all';
}
