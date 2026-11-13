<?php

namespace App\Enums;

enum AuditResult: string
{
    case Succeeded = 'succeeded';
    case Refused = 'refused';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Succeeded => 'Berhasil',
            self::Refused => 'Ditolak',
            self::Failed => 'Gagal',
        };
    }
}
