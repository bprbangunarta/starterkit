<?php

namespace App\Exceptions;

use LogicException;

class AuditLogIsAppendOnly extends LogicException
{
    public function __construct()
    {
        parent::__construct('Catatan audit hanya bisa ditambah, tidak boleh diubah atau dihapus.');
    }
}
